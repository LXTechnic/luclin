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
                if (!isset($this[$key])) {
                    throw new \InvalidArgumentException("Assign value to lists should be pair.");
                }
                $this[$key]->$to = $row->$from;
            }
        }
        return $this;
    }

    public function union($slave, string $name = null): self
    {
        if (!$slave || !isset($slave[0])) {
            return $this;
        }

        $slaveClass = get_class($slave[0]);

        if ($name) {
            $conf = [$name => $this->unionConf[$slaveClass][$name]];
        } else {
            $conf = $this->unionConf[$slaveClass];
        }

        $data = new Foundation\UnionData($this, $conf);
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

    public function seekGroup(Preset $preset, $start = null,
        string $startName = 'start'): self
    {
        $group  = $preset->parsed[count($preset->parsed) - 1];
        if (!$group->next) {
            return $this;
        }
        $vars   = [$startName => $group->next];

        // 设置分页装饰器
        $this->addDecorator('seek', [
            'type'      => 'group',
            '$preset'   => $preset->render($vars),
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
        $hasQuery   = false;
        $parsed     = $preset->parse();
        $queries    = [];
        foreach ($parsed as $applier) {
            if ($applier instanceof Contracts\Seeker) {
                continue;
            }
            $hasQuery   = true;
            $queries[]  = $applier;
        }
        if ($hasQuery) {
            $query  = $class::query(...$queries);
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