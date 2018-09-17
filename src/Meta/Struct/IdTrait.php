<?php

namespace Luclin\Meta\Struct;

use Luclin\Contracts;

/**
 *
 * @author andares
 */
trait IdTrait {

    public function fill($data): Contracts\Meta {
        if (is_object($data) && method_exists($data, 'id')) {
            $this->items['_id'] = $data->id();
        }
        return parent::fill($data);
    }

    public function id() {
        return $this->get($this->_idField());
    }

    protected function _idField(): string {
        return $this->has('id') ? 'id' : '_id';
    }
}
