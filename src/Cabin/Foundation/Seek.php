<?php

namespace Luclin\Cabin\Foundation;

use Luclin\Contracts;
use Luclin\Loader;
use Luclin\Luri;

class Seek implements Contracts\Router
{

    public function __call(string $name, array $arguments) {
        // [$path, $query, $context] = $arguments;
        $seeker = Loader::instance('luri:seek')->make($name);
        return $seeker;
    }

}