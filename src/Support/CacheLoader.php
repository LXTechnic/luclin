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
        [$key, $fun] = $arguments;
        return static::instance($name)->get($key, $fun);
    }

    public function get(string $key, callable $fun) {
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $fun();
        }
        return $this->cache[$key];
    }

    public static function cleanAll(): void {
        foreach (static::getAllInstances() as $instance) {
            $instance->cache = [];
        }
    }
}
