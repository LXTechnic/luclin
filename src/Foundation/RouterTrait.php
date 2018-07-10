<?php

namespace Luclin\Foundation;

trait RouterTrait
{
    public function __call(string $name, array $arguments) {
        [$path, $query, $context] = $arguments;
        $class = static::_nexts()[$name];
        return $class::new($path, $query, $context);
    }

    protected static function _nexts(): array {
        return [];
    }
}