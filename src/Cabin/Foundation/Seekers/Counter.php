<?php

namespace Luclin\Cabin\Foundation\Seekers;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;

class Counter implements Contracts\Endpoint, Contracts\QueryApplier, Contracts\Seeker
{
    protected $field = 'id';
    protected $direction = 'desc';

    protected $page;
    protected $take;

    public function __construct($page, int $take) {
        $this->page     = $page;
        $this->take     = $take > 500 ? 500 : $take;
    }

    public static function new(array $arguments, array $options,
        Contracts\Context $context): Contracts\Endpoint
    {
        $take       = $options['take']  ?? 20;
        $counter    = new static($arguments[0], $take);
        isset($options['field'])     && $counter->setField($options['field']);
        isset($options['direction']) && $counter->setDirection($options['direction']);
        return $counter;
    }

    public function setField(string $field): self {
        $this->field = $field;
        return $this;
    }

    public function setDirection(string $direction): self {
        $this->direction = $direction;
        return $this;
    }

    public function apply(Builder $query, array $settings): void {
        $mapping = $settings['mapping'] ?? null;
        $field   = $this->field;
        isset($mapping[$field]) && $field = $mapping[$field];

        $skip = min(100000, ($this->page - 1) * $this->take);
        $query
            ->orderBy($field, $this->direction)
            ->skip($skip)
            ->take($this->take);
    }
}