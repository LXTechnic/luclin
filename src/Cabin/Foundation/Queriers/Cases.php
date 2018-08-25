<?php

namespace Luclin\Cabin\Foundation\Queriers;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;

class Cases implements Contracts\Endpoint, Contracts\QueryApplier
{
    protected $params;

    public function __construct(array $params) {
        $this->params = $params;
    }

    public static function new(array $arguments, array $options,
        Contracts\Context $context): Contracts\Endpoint
    {
        return new static($options);
    }

    public function apply(Builder $query, array $settings): void {
        $cases = $settings['cases'] ?? null;
        if ($cases) foreach ($this->params as $name => $params) {
            $params = explode(',', $params);
            if ($params[0] === \luc\UNIT) {
                continue;
            }

            if (count($cases[$name]) == 1) { // 只有一个选项时，params完全为参数，直接选中
                $case   = $cases[$name][0];
                $assign = $params;
            } else {
                $state = array_shift($params);
                $case = $cases[$name][$state];
            }

            $pos = 0;
            foreach ($case as [$field, $operator, $value]) {
                $value === null && isset($assign[$pos]) && ($value = $assign[$pos]);
                $query->where($field, $operator, $value);

                $pos++;
            }
        }
    }
}