<?php

namespace Luclin2;

class Implicit {
    private static $registered = [

    ];

    public static function register(callable $func, string $name,
        string $space = '_'): void
    {
        static::$registered[$space][$name] = $func;
    }

    public function __call(string $name, array $arguments) {
        return $this($name, $arguments);
    }

    public function __invoke(string $name, array $arguments) {
        if (strpos($name, ':')) {
            [$space, $name] = explode(':', $name);
        } else {
            $space = '_';
        }

        $func = static::$registered[$space][$name];
        return $func(...$arguments);
    }
}
