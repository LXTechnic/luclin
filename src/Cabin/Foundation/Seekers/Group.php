<?php

namespace Luclin\Cabin\Foundation\Seekers;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;
use DB;

class Group extends Start
{
    public $next = null;

    protected $subField = 'id';
    protected $subOrder = 'desc';

    public static function new(array $arguments, array $options,
        Contracts\Context $context): Contracts\Endpoint
    {
        $group = parent::new($arguments, $options, $context);
        isset($options['subField']) && $group->subField = $options['subField'];
        isset($options['subOrder']) && $group->subOrder = $options['subOrder'];
        return $group;
    }

    public function apply(Builder $query, array $settings): void {
        $order  = $settings['order'] ?? null;
        if (!isset($order[$this->field])) {
            throw new \RuntimeException('Seek field is not allow.');
        }
        $field = $order[$this->field];

        $order  = $settings['order'] ?? null;
        if (!isset($order[$this->subField])) {
            throw new \RuntimeException('Seek sub-field is not allow.');
        }
        $subField = $order[$this->subField];

        if ($this->start) {
            $this->direction == 'desc'
                ? $query->where($field, '<=', $this->start)
                    : $query->where($field, '>=', $this->start);
        }

        // 使用distinct查询一次分组
        $take = $this->more ? ($this->take + 1) : $this->take;
        $groupQuery = clone $query;
        $groupQuery->select(DB::raw("DISTINCT ON ($field) $field"))
            ->whereNotNull($field)
            ->orderBy($field, $this->direction)
            ->take($take);
        $ids = $groupQuery->select(DB::raw("DISTINCT ON ($field) $field"))
            ->whereNotNull($field)
            ->orderBy($field, $this->direction)
            ->take($take)
            ->get()
            ->pluck($field);

        // 根据数据处理next分页信息
        if ($ids->count() > $this->take) {
            $this->next = $ids->pop();
            $ids        = $ids->toArray();
        } else {
            $ids = $ids->toArray();
        }

        // 实际查询
        $query->orderBy($field, $this->direction)
            ->orderBy($subField, $this->subOrder);
        $this->next && ($this->direction == 'desc'
            ? $query->where($field, '>', $this->next)
                : $query->where($field, '<', $this->next));
    }
}