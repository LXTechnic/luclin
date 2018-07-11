<?php

namespace Luclin\Cabin\Foundation\Seekers;

use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;

class Start implements Contracts\Endpoint, Contracts\QueryApplier
{
    protected $field = 'id';
    protected $direction = 'desc';

    protected $start;
    protected $take;
    protected $more;

    public function __construct($start, int $take, bool $more = true) {
        $this->start    = $start;
        $this->take     = $take > 500 ? 500 : $take;
        $this->more     = $more;
    }

    public static function new(array $arguments, array $options,
        Contracts\Context $context): Contracts\Endpoint
    {
        $take   = $options['take']  ?? 10;
        $more   = $options['more']  ?? true;
        $start  = new static($arguments[0], $take, boolval($more));
        isset($options['field'])     && $start->setField($options['field']);
        isset($options['direction']) && $start->setDirection($options['direction']);
        return $start;
    }

    public function setField(string $field): self {
        $this->field = $field;
        return $this;
    }

    public function setDirection(string $direction): self {
        $this->direction = $direction;
        return $this;
    }

    public function apply(Builder $query, array $_): void {
        if ($this->start) {
            $this->direction == 'desc'
                ? $query->where($this->field, '<=', $this->start)
                    : $query->where($this->field, '>=', $this->start);
        }
        $query->orderBy($this->field, $this->direction);
        $take = $this->more ? ($this->take + 1) : $this->take;
        $query->take($take);
    }
}