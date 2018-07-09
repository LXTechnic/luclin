<?php

namespace Luclin\Luri;

use Luclin\Luri;

trait RouterTrait
{
    public function __call(string $name, array $arguments) {
        [$path, $query] = $arguments;
        $class = static::_routers()[$name];
        $router = new $class();
        return $router;
    }

    protected static function _routers(): array {
        return [];
    }
}