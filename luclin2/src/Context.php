<?php

namespace Luclin2;

abstract class Context implements \ArrayAccess {
    public function __invoke(...$params) {
        return $this->handle(...$params);
    }

    abstract protected function handle();

    public function offsetExists($key): bool
    {
        return isset($this->$key);
    }

    public function offsetGet($key)
    {
        return $this->$key;
    }

    public function offsetSet($key, $value): void
    {
        $this->$key = $value;
    }

    public function offsetUnset($key)
    {
        $this->$key = null;
    }

}
