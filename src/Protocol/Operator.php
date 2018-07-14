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
        $operator = \luc\uri(static::$registers[$name].$value, ...$arguments);
        return $operator;
    }
}