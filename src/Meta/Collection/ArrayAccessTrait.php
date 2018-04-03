<?php

namespace Luclin\Meta\Collection;

/**
 * Collection和Struct接口实现类赋加ArrayAccess接口支持
 *
 * @author andares
 */
trait ArrayAccessTrait {
    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }

    public function offsetExists($offset) {
        return $this->has($offset);
    }

    public function offsetUnset($offset) {
        $this->remove($offset);
    }
}
