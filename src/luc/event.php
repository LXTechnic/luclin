<?php

namespace luc;

use Illuminate\Support\Carbon;

class event
{
    public static $alias = [];

    public static function __callStatic(string $name, array $arguments)
    {
        $class = self::$alias[$name]."\\".\luc\hyphen2class(array_shift($arguments));

        // 处理末尾闭包支持
        if (is_callable($arguments[count($arguments) - 1] ?? null)) {
            $modifier = array_pop($arguments);
        } else {
            $modifier = null;
        }

        $event = new $class(...$arguments);
        $modifier && $modifier($event);
        \event($event);
    }
}