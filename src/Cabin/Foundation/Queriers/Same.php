<?php

namespace Luclin\Cabin\Foundation\Queriers;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;

class Same implements Contracts\Endpoint, Contracts\QueryApplier
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
        $mapping = $settings['mapping'] ?? null;
        foreach ($this->params as $field => $value) {
            if ($value === \luc\UNIT) {
                continue;
            }
            isset($mapping[$field]) && $field = $mapping[$field];
            $query->where($field, $value);
        }
    }
}