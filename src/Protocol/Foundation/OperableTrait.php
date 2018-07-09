<?php

namespace Luclin\Protocol\Foundation;

use Luclin\MetaInterface;
use Luclin\Contracts;
use Luclin\Meta\Collection;
use Luclin\Loader;
use Luclin\Support\Recursive;


trait OperableTrait
{
    protected $_operators = [];

    public function getOperator(string $name, ...$arguments): ?Contracts\Operator {
        if (!isset($_operators[$name])) {
            $value = $this->get('$'.$name);
            if ($value === null) {
                return null;
            }
            $operator = Loader::instance('operator')
                ->make($name, $value, ...$arguments);
            $this->setOperator($operator, $name);
        }

        return $this->_operators[$name];
    }

    public function getOperators(string $key = null): array {
        $result = [];
        if ($key) {
            $collection = data_get($this->all(), $key);
            if ($collection) foreach ($collection as $key => $value) {
                if ($key[0] != '$') {
                    continue;
                }
                $name = substr($key, 1);
                $result[$name] = Loader::instance('operator')->make($name, $value);
            }
            return $result;
        }

        foreach ($this as $key => $value) {
            if ($key[0] != '$') {
                continue;
            }
            $name = substr($key, 1);
            $result[$name] = $this->getOperator($name);
        }
        return $result;
    }

    public function setOperator(Contracts\Operator $operator, string $name = null): self {
        if (!$name) {
            $class = get_class($operator);
            $name  = $class::getName();
        }
        $this->_operators[$name] = $operator;
        return $this;
    }

}