<?php

namespace Luclin\Flow;

class Sandbox
{
    private $_context;
    private $_domains       = [];
    private $_domainAlias   = [];
    private $_providers     = [];

    private $_roles = [];

    public function __construct(array $domains, Context $context) {
        foreach ($domains as $key => $domain) {
            $domain = new $domain();
            if (isset($this->_domains[$domain->id()])) {
                throw new \RuntimeException("Domain id ".$domain->id()." conflict.");
            }
            $this->_domains[$domain->id()] = $domain;
            if (!is_numeric($key)) {
                $this->_domainAlias[$key] = $domain->id();
            }
        }
        $this->_context = $context;
    }

    protected function domain(string $name): Domain {
        return $this->_domains[$name];
    }

    public function __call(string $name, array $arguments) {
        foreach ($this->_domains as $domain) {
            if ($domain->hasFun($name)) {
                return $domain->$name(...$arguments);
            }
        }
        return null;
    }

    public function __get($key) {
        if ($this->_context->has($key)) {
            if (!array_key_exists($key, $this->_roles)) {
                $this->loadRole($key);
            }
            return $this->_roles[$key] ?: $this->_context[$key];
        } elseif (isset($this->_domainAlias[$key])) {
            return $this->_domains[$this->_domainAlias[$key]];
        } elseif (isset($this->_domains[$key])) {
            return $this->_domains[$key];
        } else {
            // 搜索Provider
            if (!array_key_exists($key, $this->_providers)) {
                $this->_providers[$key] = null;
                foreach ($this->_domains as $domain) {
                    $provider = $domain->$key;
                    if ($provider) {
                        $this->_providers[$key] = $provider;
                        break;
                    }
                }
            }
            return $this->_providers[$key];
        }
    }

    public function __set($key, $value) {
        $this->_context[$key] = $value;
    }

    private function loadRole($key): void {
        foreach ($this->_domains as $domain) {
            if ($role = $domain->role($key)) {
                $role->assign($this->_context[$key]);
                $this->_roles[$key] = $role;
                return;
            }
        }
        $this->_roles[$key] = null;
    }
}