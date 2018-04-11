<?php

namespace Luclin\Protocol\Foundation;

use Luclin\MetaInterface;
use Luclin\Meta\Collection;
use Luclin\Support\Recursive;


trait ContrableTrait
{

    protected $_contracts   = null;

    public function addContract(string $name, $data, ?string $sub = null): self {
        $contracts  = $this->getContracts();
        $contract   = $contracts[$name] ?? [];
        $sub ? ($contract[$sub][] = $data)
            : ($contract[] = $data);
        $contracts[$name] = $contract;
        return $this;
    }

    public function setContract(string $name, $data): self {
        $contracts = $this->getContracts();
        $contracts[$name] = $data;
        return $this;
    }

    public function getContract(string $name) {
        $contracts = $this->getContracts();
        return $contracts[$name] ?? [];
    }

    public function getContracts(): Collection {
        !$this->_contracts && $this->_contracts = new Collection();
        return $this->_contracts;
    }

    public function toArray(callable $filter = null): array {
        $result = parent::toArray($filter);
        foreach ($this->getContracts() as $name => $data) {
            if (is_array($data) || $data instanceof \Traversable) {
                $toArray = new Recursive\ToArray($data, $filter);
                $result["_$name"] = $toArray();
            } else {
                $result["_$name"] = $data;
            }
        }
        return $result;
    }

    public function confirm(): MetaInterface {
        // confirm 内部所有 contract
        $it = new Recursive\TraversableIterator($this->getContracts(), function($value) {
            if (is_object($value) && $value instanceof MetaInterface) {
                $value->confirm();
                return false;
            }
        });
        foreach ($it() as $key => $value) {
            // do nothing
        }

        return parent::confirm();
    }
}