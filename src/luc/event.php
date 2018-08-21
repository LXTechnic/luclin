<?php

namespace luc;

use Illuminate\Support\Carbon;

class event
{
    public static $alias = [];

    public static function __callStatic(string $name, array $arguments)
    {
        $event = ucfirst(array_shift($arguments));
        $class = self::$alias[$name]."\\$event";
        \event(new $class(...$arguments));
    }
}