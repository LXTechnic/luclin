<?php

namespace Luclin\Meta\Collection;

use Luclin\MetaInterface;

/**
 * Collection和Struct接口实现字段变更记录
 *
 * @author andares
 */
trait ChangeLogTrait {
    protected $_originalData = [];
    protected $_isAllChanged = false;

    public function set($key, $value): MetaInterface {
        if (!$this->_isAllChanged && !array_key_exists($key, $this->_originalData)) {
            $this->_originalData[$key] = $this->get($key);
        }
        return parent::set($key, $value);
    }

    public function remove($key): MetaInterface {
        if (!$this->_isAllChanged) {
            if (array_key_exists($key, $this->_originalData)) {
                unset($this->_originalData[$key]);
            } else {
                $this->_originalData[$key] = $this->get($key);
            }
        }
        return parent::remove($key);
    }

    public function clear(): MetaInterface {
        $this->releaseOriginalData();
        $this->_isAllChanged = true;
        return parent::clear();
    }

    public function push($value): MetaInterface {
        $this->releaseOriginalData();
        $this->_isAllChanged = true;
        return parent::push($value);
    }

    public function getChanges(): array {

        // 如果数据全部为空，或者整体更新
        $data = $this->iterate()->toArray();
        if (!$data || $this->_isAllChanged) {
            return [null, null, $data];
        }

        $updated = [];
        $removed = [];
        foreach ($this->_originalData as $key => $originalValue) {
            $newValue = $this->get($key);
            if ($newValue == $originalValue) {
                continue;
            }
            if ($newValue === null) {
                $removed[] = $key;
            } else {
                $updated[$key] = $newValue;
            }
        }
        return [$updated, $removed, null];
    }

    public function getOriginalData(): array {
        return $this->_originalData + $this->iterate()->toArray();
    }

    public function releaseOriginalData(): MetaInterface {
        $this->_originalData = [];
        $this->_isAllChanged = false;
        return $this;
    }
}
