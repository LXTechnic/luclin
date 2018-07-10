<?php

namespace Luclin\Contracts;

interface Router
{
    public static function new(array $arguments, array $options, Context $context);
}