<?php

namespace Luclin\Meta;

use Luclin\MetaInterface;
use Luclin\Support\Recursive;

/**
 *
 * @author andares
 */
trait CollectionTrait
{
    use Collection\ArrayAccessTrait,
        Collection\FillableTrait,
        Collection\ChangeLogTrait,
        Collection\ToArrayTrait,
        Collection\OverloadedTrait,
        Collection\ToBinTrait;

    /**
     * @return array
     */
    public function keys(): array {
        return array_keys($this->items);
    }

    /**
     *
     * @param int|string $key
     * @param mixed $value
     * @return MetaInterface
     */
    public function set($key, $value): MetaInterface {
        $key === null ? $this->items[] = $value : $this->items[$key] = $value;
        return $this;
    }

    /**
     *
     * @param int|string $key
     * @param mixed $default
     * @return mixed|null
     */
    public function get($key, $default = null) {
        return $this->items[$key] ?? $default;
    }

    /**
     * 替换集合中已经存在的键值
     *
     * @param array $items
     * @return MetaInterface
     */
    public function replace(array $items): MetaInterface {
        foreach ($items as $key => $value) {
            $this->has($key) && $this->set($key, $value);
        }
        return $this;
    }

    /**
     * 返回一个遍历内部数据的迭代器
     *
     * @return iterable
     */
    public function iterate(): iterable {
        foreach ($this->items as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     *
     * @param int|string $key
     * @return bool
     */
    public function has($key): bool {
        return isset($this->items[$key]);
    }

    /**
     * 移除一个单元
     *
     * @param int|string $key
     * @return MetaInterface
     */
    public function remove($key): MetaInterface {
        unset($this->items[$key]);
        return $this;
    }

    /**
     *
     * @return MetaInterface
     */
    public function clear(): MetaInterface {
        $this->items = [];
        return $this;
    }

    /**
     *
     * @return MetaInterface
     * @throws \UnexpectedValueException
     */
    public function confirm(): MetaInterface {
        // confirm 内部所有meta
        $it = new Recursive\TraversableIterator($this, function($value) {
            if (is_object($value) && $value instanceof MetaInterface) {
                $value->confirm();
                return false;
            }
        });

        $methods = $this->afterConfirmOnce();
        foreach ($it() as $key => $value) {
            foreach ($methods as $method) {
                $call = [$this, $method];
                $call($key, $value);
            }
        }

        // 整体confirm勾子
        $methods = $this->afterConfirm();
        if ($methods && is_array($methods)) {
            foreach ($methods as $method) {
                $call = [$this, $method];
                $call();
            }
        }

        return $this;
    }

    /**
     *
     * @return string[]
     */
    protected function afterConfirmOnce(): array {
        return [];
    }

    /**
     * afterConfirm的注册方法，返回一个闭包数组
     *
     * @return string[]|null
     */
    protected function afterConfirm() {
        return [];
    }

}
