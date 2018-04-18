<?php

namespace Luclin\Support;

use Luclin\Foundation;

/**
 */
class CacheLoader
{
    use Foundation\SingletonNamedTrait;

    private $cache = [];

    public static function __callStatic(string $name, array $arguments)
    {
        [$key, $func] = $arguments;
        return static::instance($name)->get($key, $func);
    }

    public function get(string $key, callable $func) {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $func();
        }
        return $this->cache[$key];
    }

    public static function cleanAll(): void {
        foreach (static::getAllInstances() as $instance) {
            $instance->cache = [];
        }
    }
}
