<?php

namespace Luclin\Contracts;

interface Endpoint
{
    public static function new(array $arguments, array $options, Context $context);
}