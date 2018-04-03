<?php

namespace Luclin\Protocol\Operators;

use Luclin\Loader;
use Luclin\Contracts;
use Luclin\Uri;
use Luclin\Protocol\Operator;

class Construct implements Contracts\Operator
{
    protected $uri;

    public function __construct(string $value) {
    }

}