<?php

namespace Luclin;

use Luclin\Contracts;

/**
 */
class Loader
{
    use Foundation\SingletonNamedTrait;

    protected static $sings = [];

    protected $prefix = 0;

    protected $cache = [];

    protected $registers = [];

    public static function uri($uri) {
        if (is_string($uri)) {
            $uri = app(Uri::class, [
                'uri'   => $uri,
            ]);
        }
        $constructArguments = explode(',', $uri->getQuery()['$construct'] ?? '');
        $object = static::instance($uri->getRoot())
            ->make($uri->getPath(), ...$constructArguments);
        return $object;
    }

    public function register(...$namespaces): self {
        !isset($this->registers[$this->prefix]) && $this->registers[$this->prefix] = [];

        if (is_callable($namespaces[0])) {
            $builder = array_shift($namespaces);
        } else {
            $builder = true;
        }
        foreach ($namespaces as $namespace) {
            $this->registers[$this->prefix][$namespace] = $builder;
        }

        $this->prefix = 0;
        return $this;
    }

    public function prefix(string $prefix): self {
        $this->prefix = $prefix;
        return $this;
    }

    public function get(string $name, string $slash = '/'): ?array {
        if (!array_key_exists($name, $this->cache)) {
            $this->cache[$name] = null;

            $prefix = strstr($name, ':', true) ?: 0;
            $class  = $prefix ? (substr($name, strlen($prefix) + 1)) : $name;
            $class  = strtr(ucwords($class, $slash), $slash, '\\');
            foreach ($this->registers[$prefix] as $namespace => $builder) {
                $find = "$namespace\\$class";
                if (class_exists($find)) {
                    $this->cache[$name] = [$find, $builder];
                    break;
                }
            }
        }
        return $this->cache[$name];
    }

    public function make(string $name, ...$arguments) {
        [$class, $builder] = $this->get($name) ?: [null, null];
        if (!$class) {
            return null;
        }

        return is_callable($builder)
            ? $builder($class, ...$arguments) : (new $class(...$arguments));
    }

    public function sing(string $name, ...$arguments) {
        [$class, $builder] = $this->get($name) ?: [null, null];
        if (!$class) {
            return null;
        }

        if (is_callable($builder)) {
            return static::$signs[$class] ?? (static::$signs[$class] = $builder($class, ...$arguments));
        }
        return $class::instance(...$arguments);
    }
}
