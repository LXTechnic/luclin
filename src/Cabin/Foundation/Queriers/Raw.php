<?php

namespace Luclin\Cabin\Foundation\Queriers;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;

class Raw implements Contracts\Endpoint, Contracts\QueryApplier
{
    protected $name;
    protected $params;

    public function __construct(string $name, array $params) {
        $this->name     = $name;
        $this->params   = $params;
    }

    public static function new(array $arguments, array $options,
        Contracts\Context $context): Contracts\Endpoint
    {
        return new static(array_shift($arguments), $options);
    }

    public function apply(Builder $query, array $settings): void {
        ['template' => $template, 'lists' => $lists] = $settings['raw'][$this->name];
        $params = [];
        foreach ($this->params as $k => $v) {
            if ($v === \luc\UNIT) {
                continue;
            }

            // 暂未处理 lists
            // if (in_array($k, $lists)) {
            // }
            $params[$k] = $v;
        }
        if (!$params) {
            return;
        }

        $condition = \luc\padding($template, $params);
        $query->whereRaw($condition);
    }
}