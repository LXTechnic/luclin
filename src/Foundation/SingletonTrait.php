<?php

namespace Luclin\Foundation;

trait SingletonTrait
{
    protected static $_instances = [];

    public static function instance(...$arguments) {
        if (!isset(static::$_instances[static::class])) {
            static::$_instances[static::class] = new static(...$arguments);
        }
        return static::$_instances[static::class];
    }
}