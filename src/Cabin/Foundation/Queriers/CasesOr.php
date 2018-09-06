<?php

namespace Luclin\Cabin\Foundation\Queriers;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;

class CasesOr implements Contracts\Endpoint, Contracts\QueryApplier
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
        $cases = $settings['casesOr'] ?? null;

        $conditions = [];
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
                if ($value === null && isset($assign[$pos])) {
                    $value = $assign[$pos];
                    $pos++;
                } elseif (preg_match('/^\:([0-9]+)$/', $value, $matches)
                    && isset($assign[$matches[1]]))
                {
                    $value = $assign[$matches[1]];
                }

                $conditions[] = [$field, $operator, $value];
            }
        }

        $query->where(function($query) use ($conditions) {
            foreach ($conditions as [$field, $operator, $value]) {
                $query->orWhere($field, $operator, $value);
            }
        });
    }
}