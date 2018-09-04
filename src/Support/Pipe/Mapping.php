<?php

namespace Luclin\Support\Pipe;

use Luclin\Contracts;
use Luclin\Meta;

class Mapping extends Meta\Collection implements Contracts\CallAgent
{
    public function __call($name, $arguments) {
        $func = $this->$name;
        return $func->call($this, ...$arguments);
    }
}