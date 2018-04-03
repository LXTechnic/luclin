<?php

namespace Luclin\Protocol;

use Luclin\Meta\Collection;
use Luclin\Uri;
use Luclin\Support\Recursive;

class Container extends Collection {
    use Foundation\ContrableTrait,
        Foundation\OperableTrait;

    public function __set($name, $value) {
        parent::__set($name, $value);
        $value instanceof DomainInterface && $this->applyDecorators($name, $value);
    }

    public function offsetSet($offset, $value) {
        parent::offsetSet($offset, $value);
        $value instanceof DomainInterface && $this->applyDecorators($offset, $value);
    }

    public function applyDecorators(string $name,
        DomainInterface $domain = null)
    {
        $decorators = $domain ?
            $domain->getDecorators() : $this->get($name)->getDecorators();
        $decorators
            && ($toArray = new Recursive\ToArray($decorators))
                && $this->set("@$name", $toArray());
    }
}
