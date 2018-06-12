<?php

namespace Luclin\Domain;

abstract class Role
{
    protected $_metohds;

    public function __construct()
    {
        $this->_methods = $this->makeMethods();
    }

    public function getMethod(string $name): callable {
        return $this->_methods[$name];
    }

    public function getMethods(): array {
        return $this->_methods;
    }

    public function getMethodNames(): array {
        return array_keys($this->_methods);
    }

    // TODO: 回头看能不能改成分单个method按需生成
    abstract protected function makeMethods(): array;
}

