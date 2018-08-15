<?php

namespace Luclin\Meta\Collection;

/**
 *
 * @author andares
 */
trait IdTrait {
    public function id() {
        return $this->get($this->_idField());
    }

    protected function _idField(): string {
        return $this->has('id') ? 'id' : '_id';
    }
}
