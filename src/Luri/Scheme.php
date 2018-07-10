<?php

namespace Luclin\Luri;

use Luclin\Contracts;
use Luclin\Foundation\{
    SingletonTrait,
    RouterTrait
};

abstract class Scheme
{
    use SingletonTrait,
        RouterTrait;
}