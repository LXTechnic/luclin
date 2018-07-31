<?php

namespace Luclin\Protocol;

use Luclin\Meta\Collection;
use Luclin\Support\Recursive;

class Container extends Collection
{
    use Foundation\ContrableTrait,
        Foundation\OperableTrait {
            Foundation\ContrableTrait::toArray  as contract2array;
            Foundation\OperableTrait::toArray   as operator2array;
        }

    public function __set($name, $value) {
        parent::__set($name, $value);
        $value instanceof FieldInterface && $this->applyDecorators($name, $value);
    }

    public function offsetSet($offset, $value) {
        parent::offsetSet($offset, $value);
        $value instanceof FieldInterface && $this->applyDecorators($offset, $value);
    }

    public function applyDecorators(string $name,
        FieldInterface $domain = null)
    {
        $decorators = $domain ?
            $domain->getDecorators() : $this->get($name)->getDecorators();
        $decorators
            && ($toArray = new Recursive\ToArray($decorators))
                && $this->set("@$name", $toArray());
    }

    public function toArray(callable $filter = null): array {
        $result = parent::toArray($filter);
        return $this->appendOperators2Array($this->appendContracts2Array($result));
    }
}
