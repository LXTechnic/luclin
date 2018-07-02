<?php

namespace Luclin\Flow;

abstract class Role
{
    protected $_raw;

    public function assign(object $object): self {
        $this->_raw = $object;
        return $this;
    }

    public function raw() {
        return $this->_raw;
    }

    public function __call(string $name, array $arguments) {
        return $this->_raw->$name(...$arguments);
    }

    public function __get(string $name) {
        return $this->_raw->$name;
    }

    public function __set(string $name, $value) {
        $this->_raw->$name = $value;
    }
}

