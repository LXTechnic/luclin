<?php

namespace Luclin\Protocol\Foundation;

use Luclin\MetaInterface;
use Luclin\Contracts;
use Luclin\Meta\Collection;
use Luclin\Protocol\Operator;
use Luclin\Support\Recursive;


trait OperableTrait
{
    protected $_operators = [];

    public function getOperators(string $name, ...$arguments): ?array {
        if (!isset($_operators[$name])) {
            $value = $this->get('$'.$name);
            if ($value === null) {
                throw new \RuntimeException("The operator [$name] not found.");
            }
            if (is_array($value)) {
                $operators = [];
                foreach ($value as $row) {
                    $operators[] = Operator::make($name, $row, ...$arguments);
                }
                $this->setOperators($name, $operators);
            } else {
                $operator = Operator::make($name, $value, ...$arguments);
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
                $result[$name] = Operator::make($name, $value);
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