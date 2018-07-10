<?php

namespace Luclin\Protocol\Foundation;

use Luclin\MetaInterface;
use Luclin\Contracts;
use Luclin\Meta\Collection;
use Luclin\Luri;
use Luclin\Loader;
use Luclin\Support\Recursive;


trait OperableTrait
{
    protected $_operators = [];

    public function getOperators(string $name, ...$arguments): ?array {
        if (!isset($_operators[$name])) {
            $value = $this->get('$'.$name);
            if ($value === null) {
                return null;
            }
            if (is_array($value)) {
                $operators = [];
                foreach ($value as $v) {
                    $operators = Loader::instance('luri:operator')
                        ->make($name, $value, ...$arguments);
                }
                $this->setOperators($name, $operators);
            } else {
                $operator = Loader::instance('luri:operator')
                    ->make($name, $value, ...$arguments);
                $this->setOperators($name, [$operator]);
            }
        }

        return $this->_operators[$name];
    }

    public function getAllOperators(string $key = null): array {
        $result = [];
        if ($key) {
            $collection = data_get($this->all(), $key);
            if ($collection) foreach ($collection as $key => $value) {
                if ($key[0] != '$') {
                    continue;
                }
                $name = substr($key, 1);
                $result[$name] = Loader::instance('luri:operator')->make($name, $value);
            }
            return $result;
        }

        foreach ($this as $key => $value) {
            if ($key[0] != '$') {
                continue;
            }
            $name = substr($key, 1);
            $result[$name] = $this->getOperators($name);
        }
        return $result;
    }

    public function setOperators(string $name, array $operators): self {
        $this->_operators[$name] = $operators;
        return $this;
    }

}