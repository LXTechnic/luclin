<?php

namespace Luclin\Cabin;

use Luclin\Contracts;
use Luclin\Foundation;
use Luclin\Meta;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class Container extends Meta\Collection
{
    use Foundation\SingletonNamedTrait;

    private $_cacheLimit = 3000;
    private $_index = [];
    private $_new = [];

    public function set($key, $value): Contracts\Meta {
        if ($this->count() > $this->_cacheLimit) {
            // 清理
            $count = 0;
            $limit = floor($this->_cacheLimit / 2);
            foreach ($this->items as $k => $_) {
                unset($this->items[$k]);
                $count++;
                if ($count >= $limit) {
                    break;
                }
            }
        }

        if ($key === null) {
            $id = \luc\idgen::sorted62(10, 1);
            $this->_new[$id] = $value->setNewBindId($id);
        } else {
            parent::set($key, $value);
        }
        return $this;
    }

    public function transformNewModel(string $newBindId): self {
        $this->set($this->_new[$newBindId]->findId(), $this->_new[$newBindId]);
        unset($this->_new[$newBindId]);
        return $this;
    }

    public function index(array $feature, $id = null) {
        ksort($feature);
        $key = serialize($feature);

        if (!$id) {
            $id = $this->_index[$key] ?? null;
            if (!$id) {
                return $id;
            }
            $row = $this->get($id);
        } else {
            $row = $this->get($id);
            $row && $this->_index[$key] = $id;
        }
        return $row;
    }

    /**
     *
     * @return Contracts\Meta
     */
    public function clear(): Contracts\Meta {
        $this->_index = [];
        return parent::clear();
    }

    public function release(): self {
        foreach ($this->all() as $model) {
            is_object($model) && $model->save();
        }

        // 同时处理新建的
        foreach ($this->_new as $model) {
            $model->save();
        }
        return $this;
    }

}