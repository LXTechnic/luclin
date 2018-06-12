<?php

namespace luc;

use Luclin\Support\Recursive;

class helper
{
    public static $addons = [];

    public static function __callStatic(string $name, array $arguments)
    {
        $method = self::$addons[$name];
        return $method(...$arguments);
    }

    public static function toArray(iterable $iterable,
        callable $filter = null): array
    {
        $toArray = new Recursive\ToArray($iterable, $filter);
        return $toArray();
    }

    public static function timer() {
        static $start = null;
        if (!$start) {
            $start = \PHP_VERSION_ID >= 70300 ? hrtime(true) : microtime(true);
            return $start;
        }
        $elapsed = \PHP_VERSION_ID >= 70300 ? hrtime(true) : microtime(true) - $start;
        $start   = null;
        return $elapsed;
    }

}