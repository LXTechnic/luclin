<?php

namespace Luclin\Domain;

use Luclin\Foundation;
use Luclin\Flow;

abstract class Provider
{
    use Foundation\SingletonTrait;

    const TYPE_ACTION       = 1;
    const TYPE_INSPECTOR    = 2;
    const TYPE_CASER        = 3;

    public static $type = self::TYPE_ACTION;

    protected $_domain;
    protected $_context;

    public function setDomain(\Luclin\Domain $domain): self {
        $this->_domain      = $domain;
        return $this;
    }

    public function setContext(Flow\Context $context): self {
        $this->_context     = $context;
        return $this;
    }

    public function domain(): \Luclin\Domain {
        return $this->_domain;
    }

    public function context(): Flow\Context {
        return $this->_context;
    }

    public function __get(string $name) {
        if ($this->_context->has($name)) {
            return $this->_domain->asRole($this->_context[$name], $name);
        }
        return null;
    }

    public function __set(string $name, $value) {
        $this->_context->set($name, $value);
    }
}
