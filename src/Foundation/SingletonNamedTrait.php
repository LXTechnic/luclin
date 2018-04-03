<?php

namespace Luclin\Foundation;

trait SingletonNamedTrait
{
    protected static $_instances = [];

    public static function instance($name, ...$arguments) {
        $key = static::class."-$name";
        if (!isset(static::$_instances[$key])) {
            static::$_instances[$key] = new static(...$arguments);
        }
        return static::$_instances[$key];
    }
}