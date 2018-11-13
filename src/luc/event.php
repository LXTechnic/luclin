<?php

namespace luc;

use Illuminate\Support\Carbon;

class event
{
    public static $alias = [];

    public static function __callStatic(string $name, array $arguments)
    {
        $class = self::$alias[$name]."\\".\luc\hyphen2class(array_shift($arguments));
        \event(new $class(...$arguments));
    }
}