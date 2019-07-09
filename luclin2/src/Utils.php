<?php

namespace Luclin2;

class Utils {
    public static function __callStatic(string $name, array $arguments) {
        $class  = "Luclin2\\Utils\\".ucfirst($name);
        // dd($arguments);
        $func   = $class(...$arguments);
        return $func();
    }
}
