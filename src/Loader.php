<?php

namespace Luclin;

use Luclin\Contracts;

/**
 */
class Loader
{
    use Foundation\SingletonNamedTrait;

    protected $cache = [];

    protected $registers = [];

    public static function uri($uri) {
        // if (is_string($uri)) {
        //     $uri = app(Uri::class, [
        //         'uri'   => $uri,
        //     ]);
        // }
        // $constructArguments = explode(',', $uri->getQuery()['$construct'] ?? '');
        // $object = static::instance($uri->getRoot())
        //     ->make($uri->getPath(), ...$constructArguments);
        return $object;
    }

    public function register(...$namespaces): self {
        if (is_callable($namespaces[0])) {
            $builder = array_shift($namespaces);
        } else {
            $builder = true;
        }
        foreach ($namespaces as $namespace) {
            $this->registers[$namespace] = $builder;
        }

        return $this;
    }

    public function class(string $name, string $slash = '/'): ?array {
        if (!array_key_exists($name, $this->cache)) {
            $this->cache[$name] = null;

            $class  = strtr(ucwords($name, $slash), $slash, '\\');
            foreach ($this->registers as $namespace => $builder) {
                $fullName = "$namespace\\$class";
                if (class_exists($fullName)) {
                    $this->cache[$name] = [$fullName, $builder];
                    break;
                }
            }
        }
        return $this->cache[$name];
    }

    public function make(string $name, ...$arguments) {
        [$class, $builder] = $this->class($name) ?: [null, null];
        if (!$class) {
            return null;
        }

        if (is_callable($builder)) {
            return $builder($class, ...$arguments);
        }
        if (method_exists($class, 'instance')) {
            return $class::instance(...$arguments);
        }
        if (method_exists($class, 'new')) {
            return $class::new(...$arguments);
        }
        return new $class(...$arguments);
    }

}
