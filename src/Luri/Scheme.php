<?php

namespace Luclin\Luri;

use Luclin\Luri;
use Luclin\Contracts;
use Luclin\Foundation\SingletonTrait;

abstract class Scheme implements Contracts\Router
{
    use SingletonTrait,
        RouterTrait;
}