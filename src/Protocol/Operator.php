<?php

namespace Luclin\Protocol;

use Luclin\Contracts;
use Luclin\Luri;

class Operator
{
    private static $registers = [];

    public static function register(string $name, string $prefix): void {
        static::$registers[$name] = $prefix;
    }

    public static function make($name, $value, ...$arguments): Contracts\Operator {
        $luri       = Luri::createByUrl(static::$registers[$name].$value);
        [$operator] = $luri->resolve(...$arguments);
        return $operator;
    }
}