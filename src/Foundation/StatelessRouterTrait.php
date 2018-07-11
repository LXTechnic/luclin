<?php

namespace Luclin\Foundation;

use Luclin\Contracts;

trait StatelessRouterTrait
{
    public static function new(array $arguments, array $options,
        Contracts\Context $context): Contracts\Router
    {
        return new static();
    }

}