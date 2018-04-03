<?php

namespace Luclin\Protocol\Foundation;

use Luclin\MetaInterface;
use Luclin\Meta\Collection;

trait DecorableTrait
{

    protected $_decorators  = [];

    public function addDecorator(string $name, $data): self {
        $this->_decorators[$name][] = $data;
        return $this;
    }

    public function getDecorators(): array {
        return $this->_decorators;
    }
}