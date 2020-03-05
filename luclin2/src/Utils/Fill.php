<?php

namespace Luclin2\Utils;

class Fill
{
    protected $from;
    protected $to;
    protected $mask;

    public function __construct(iterable $traversable,
        $arrayable, array $mask = [])
    {
        $this->from = $traversable;
        $this->to   = $arrayable;
        $this->mask = $mask;
    }

    public function __invoke() {
        if ($this->mask) foreach ($this->mask as $field) {
            $this->from[$field] && $this->to[$field] = $this->from[$field];
        } else foreach ($this->from as $field => $value) {
            $this->to[$field] = $value;
        }
        return $this->to;
    }
}