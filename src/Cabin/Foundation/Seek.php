<?php

namespace Luclin\Cabin\Foundation;

use Luclin\Contracts;
use Luclin\Foundation;
use Luclin\Loader;

class Seek implements Contracts\Router, Contracts\Operator
{
    use Foundation\StatelessRouterTrait;

    public function __call(string $name, array $arguments) {
        [$path, $query, $context] = $arguments;
        return Loader::instance('seeker')->make($name, $path, $query, $context);
    }

}