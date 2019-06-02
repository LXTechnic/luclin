<?php

namespace Luclin\Cabin\Foundation\Queriers;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;

class OrIn implements Contracts\Endpoint, Contracts\QueryApplier
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
            $query->where(function($query) use ($field, $value) {
                is_string($value) && $value = explode(',', $value);
                foreach ($value as $v) {
                    $query->orWhere($field, $v);
                }
            });
        }
    }
}