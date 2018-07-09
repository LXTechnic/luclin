<?php

namespace Luclin\Routers;

use Luclin\Contracts;
use Luclin\Loader;
use Luclin\Luri;

class Seek implements Contracts\Router
{

    public function __call(string $name, array $arguments) {
        $seeker = Loader::instance('luri:seek')->make($name);
        dd($seeker);
        return $seeker();
    }

}