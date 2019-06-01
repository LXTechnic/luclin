<?php

namespace Luclin;

use Illuminate\Support\Collection;

class Variant
{
    private $arguments = [];

    public function __construct(...$arguments) {
        $this->arguments = $arguments;
    }

    public function __invoke(): Collection {
        return collect($this->arguments);
    }
}
