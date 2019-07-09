<?php

namespace Luclin2;

class Utils {
    public function __call(string $name, array $arguments) {
        $class  = "Luclin2\\Utils\\".ucfirst($name);
        // dd($arguments);
        $func   = new $class(...$arguments);
        return $func();
    }
}
