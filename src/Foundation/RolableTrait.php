<?php

namespace Luclin\Foundation;

use Luclin\Domain;

trait RolableTrait
{
    protected $_roles = [];
    protected $_roleMethods = [];

    public function assume(string $name, Domain\Role $role): self {
        if (!isset($this->_roles[$name])) {
            $this->_roles[$name]    = $role;
            $this->_roleMethods     = array_merge($this->_roleMethods,
                $role->getMethods());
        }
        return $this;
    }

    public function cleanRoles(): self {
        $this->_roles = $this->_rolesMethods = [];
        return $this;
    }

    public function __call($name, $arguments) {
        if (isset($this->_roleMethods[$name])) {
            $method = $this->_roleMethods[$name];
            return $method->call($this, ...$arguments);
        }
        return parent::__call($name, $arguments);
    }
}