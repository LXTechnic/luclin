<?php

namespace Luclin\Protocol;

use Luclin\MetaInterface;
use Luclin\Meta\Collection;
use Luclin\Uri;

class Lists extends Collection implements DomainInterface
{
    use Foundation\DecorableTrait;

    protected $unionConf;

    public function __construct(string $class, $master, array $unionConf) {
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
        // TODO: 这里要不要转数组还要再看看
        !is_array($slave) && $slave = $slave->toArray();

        $data = new Foundation\UnionData($this,
             $alias ? [$alias => [$class, $masterField, $slaveField]]
                : $this->unionConf[$slaveClass]);
        $data($slave);
        return $this;
    }

    public function unionCall(string $alias, $func): self {
        foreach ($this as $row) {
            foreach ($row->getUnion($alias) as $union) {
                if (is_callable($func)) {
                    $func($row, $union);
                } else {
                    $union->$func($row);
                }
            }
        }
        return $this;
    }

    /**
     * 迁移后要大改，这里应该不接收uri，只接收operator
     */
    public function more($handler, $start = null): self {
        // 拿取下一页第一行vo
        if ($handler instanceof Uri) {
            [$limit] = $handler->fragment()->getSlice();
        } else {
            [$limit] = $handler->getSlice();
        }

        $count = $this->count();
        if (!$count || $count <= $limit) {
            return $this;
        }
        $row = $this->pop();

        // 取 start 有三种模式
        // 给null或id时默认取getId()
        // 给字串时取该字串属性
        // 给callback时执行callback
        if (is_string($start) && $start != 'id') {
            $start = $row->$start;
        } elseif (is_callable($start)) {
            $start = $start($row);
        } else {
            $start = $row->getId();
        }

        if ($handler instanceof Uri) {
            $handler->fragment()->setSlice(null, $start)->regress();
        } else {
            $handler->setSlice(null, $start);
        }

        if ($handler instanceof Operators\Preset) {
            $decorator = [
                '$preset' => "$handler",
            ];
        } else {
            $decorator = [
                '$query' => $handler->render('path', 'query', 'fragment'),
            ];
        }
        $this->addDecorator('more', $decorator);

        return $this;
    }
}