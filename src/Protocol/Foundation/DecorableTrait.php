<?php

namespace Luclin\Protocol\Foundation;

use Luclin\Meta\Collection;

trait DecorableTrait
{

    protected $_decorators  = [];

    public function addDecorator(string $name, $data): self {
        $this->_decorators[$name][] = $data;
        return $this;
    }

    public function setDecorator(string $name, $data): self {
        $this->_decorators[$name] = $data;
        return $this;
    }

    public function getDecorator(string $name) {
        return $this->_decorators[$name] ?? null;
    }

    public function getDecorators(): array {
        return $this->_decorators;
    }
}