<?php

namespace luc;

class conf
{
    protected $_prefix;
    protected $_alias;

    public static function prefix(string $prefix): self {
        $instance = new static();
        $instance->_prefix = $prefix;
        return $instance;
    }

    public function alias(string $alias, string $path): self {
        $this->_alias[$alias] = $path;
        return $this;
    }

    public function override(string $name, $value): self {
        $this->$name = $value;
        return $this;
    }

    public function __get(string $key) {
        $path = $this->_alias[$key] ?? $key;
        $path = $this->_prefix ? "$this->_prefix.$path" : $path;
        return \config($path);
    }
}