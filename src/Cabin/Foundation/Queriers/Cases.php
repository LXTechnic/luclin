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
        if ($cases) foreach ($this->params as $name => $state) {
            $case = $cases[$name][$state];
            foreach ($case as [$field, $operator, $value]) {
                $query->where($field, $operator, $value);
            }
        }
    }
}