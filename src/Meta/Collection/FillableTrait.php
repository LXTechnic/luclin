<?php

namespace Luclin\Meta\Collection;

use Luclin\Contracts;
use Luclin\Meta\Struct;

use Illuminate\Contracts\Support\Arrayable;


/**
 * Collection和Struct接口实现类赋加fill相关接口支持
 *
 * @author andares
 */
trait FillableTrait {

    /**
     * 填充时排除字段
     *
     * @var array
     */
    protected $_excludeKeys = [];

    /**
     * 填充数据方法
     *
     * @param \Traversable|array $data
     * @return Contracts\Meta
     * @throws \UnexpectedValueException
     */
    public function fill($data): Contracts\Meta {
        if ($$data === null) {
            return $this;
        }
        if (!is_array($data) && !($data instanceof \Traversable)) {
            if ($data instanceof Arrayable) {
                $data = $data->toArray();
            } else {
                throw new \UnexpectedValueException("fill data error");
            }
        }

        foreach ($data as $key => $value) {
            if (isset($this->_excludeKeys[$key])) {
                continue;
            }
            $this->set($key, $data[$key]);
        }
        return $this;
    }

    /**
     * 添加要排除的字段。不传参数为添空之前添加的排除字段。
     *
     * @param array $keys
     * @return Contracts\Meta
     */
    public function exclude(...$keys): Contracts\Meta {
        if ($keys) {
            foreach ($keys as $key) {
                $this->_excludeKeys[$key] = 1;
            }
        } else {
            $this->_excludeKeys = [];
        }
        return $this;
    }

}
