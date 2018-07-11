<?php

namespace Luclin\Protocol;

use Luclin\MetaInterface;
use Luclin\Meta\Collection;
use Luclin\Luri\Preset;

class Lists extends Collection implements FieldInterface
{
    use Foundation\DecorableTrait;

    protected $unionConf;

    public function __construct(string $class,
        iterable $master, array $unionConf)
    {
        foreach ($master as $row) {
            $instance   = (new $class())->fill($row);
            $this[] = $instance;
        }
        $this->unionConf = $unionConf;
    }

    public function __call($method, $parameters): self {
        foreach ($this as $row) {
            $row->$method(...$parameters);
        }
        return $this;
    }

    public function assign(string $field, $collection, string $fromField): self {
        foreach ($collection as $key => $row) {
            $this[$key]->$field = $fromField;
        }
        return $this;
    }

    public function union($slave, string $alias = null,
        string $masterField = 'id', string $slaveField = 'id',
        string $class = null): self
    {
        if (!$slave || !isset($slave[0])) {
            return $this;
        }

        $slaveClass = get_class($slave[0]);

        // if (!is_array($slave)) {
        //     if ($slave instanceof MetaInterface) {
        //         $slave->confirm();
        //     }
        //     $slave = $slave->toArray();
        // }

        $data = new Foundation\UnionData($this,
             $alias ? [$alias => [$class, $masterField, $slaveField]]
                : $this->unionConf[$slaveClass]);
        $data($slave);
        return $this;
    }

    public function unionCall(string $alias, $fun, ...$params): self {
        foreach ($this as $row) {
            foreach ($row->getUnion($alias) as $union) {
                if (is_callable($fun)) {
                    $fun($row, $union, ...$params);
                } else {
                    $union->$fun($row, ...$params);
                }
            }
        }
        return $this;
    }

    public function more(Preset $preset, $start = null,
        string $takeName = 'take', $startName = 'start'): self
    {
        // 判断是否到末尾，并取下一页首条记录
        $count = $this->count();
        if (!$count || $count <= $preset->$takeName) {
            return $this;
        }
        $row = $this->pop();

        // 取 start 有三种模式
        // 给null或id时默认取 id()
        // 给字串时取该字串属性
        // 给callback时执行callback
        if (is_string($start) && $start != 'id') {
            $start = $row->$start;
        } elseif (is_callable($start)) {
            $start = $start($row);
        } else {
            $start = $row->id();
        }

        // 设置分页装饰器
        $this->addDecorator('more', [
            '$preset'   => $preset->render([$startName => $start]),
        ]);
        return $this;
    }
}