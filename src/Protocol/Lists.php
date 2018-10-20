<?php

namespace Luclin\Protocol;

use Luclin\Contracts;
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

    public function assign($collection, $fromField, $toField = null): self {
        !is_array($fromField) && $fromField = [$fromField];
        $toField && !is_array($toField) && $toField = [$toField];
        foreach ($collection as $key => $row) {
            foreach ($fromField as $pos => $from) {
                $to = $toField[$pos] ?? $from;
                $this[$key]->$to = $row->$from;
            }
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

    public function seekStart(Preset $preset, $start = null,
        string $takeName = 'take', string $startName = 'start'): self
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
        $this->addDecorator('seek', [
            'type'      => 'start',
            '$preset'   => $preset->render([$startName => $start]),
        ]);
        return $this;
    }

    public function seekCounter(Preset $preset, string $pageName = 'page',
        string $takeName = 'take'): self
    {
        // 取模型及类
        $model  = $this->first();
        if (!$model) {
            return $this;
        }
        $model  = $model->raw();
        $class  = get_class($model);

        // 判断有没有query，如果没有的话走估算方案
        $hasQuery = false;
        foreach ($parsed = $preset->parse() as $applier) {
            if (!($applier instanceof Contracts\Seeker)) {
                $hasQuery = true;
                break;
            }
        }
        if ($hasQuery) {
            $query  = $class::query(...$preset->parse());
            $total  = $class::countLimit($query, 100000);
        } else {
            $total  = min(100000, $class::estimateLiveRows());
        }

        // 计算范围
        $range  = $preset->range ?: 5;
        $first  = 1;
        $last   = intval(ceil($total / $preset->$takeName));
        $cursor = $preset->$pageName;
        $middle = ($cursor == $first || $cursor == $last) ? [] : [$cursor];
        for ($i = 1; $i < $range; $i++) {
            $page = $cursor - $i;
            if ($page > $first) {
                array_unshift($middle, $page);
            }

            $page = $cursor + $i;
            if ($page < $last) {
                $middle[] = $page;
            }
        }
        if ($cursor == $first) {
            $current = 'first';
        } elseif ($cursor == $last) {
            $current = 'last';
        } else {
            $current = array_search($cursor, $middle);
        }

        // 分页数据库成
        $data = [
            'type'      => 'counter',
            'total'     => $total,
            'pages'     => $last,
            'current'   => $current,
            '$preset'   => [
                'first' => $preset->render([$pageName => $first]),
            ],
        ];
        $first != $last
            && $data['$preset']['last'] = $preset->render([$pageName => $last]);
        foreach ($middle as $page) {
            $data['$preset']['middle'][] = $preset->render([$pageName => $page]);
        }

        // 设置分页装饰器
        $this->addDecorator('seek', $data);
        return $this;
    }
}