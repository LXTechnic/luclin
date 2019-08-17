<?php

namespace Luclin2\Utils;

class United
{
    protected $root;

    public function __construct(iterable $traversable)
    {
        $this->root = $traversable;
    }

    public function __invoke(): array {
        return $this->apply($this->root);
    }

    private function apply(iterable $traversable): array {
        $result = [];
        foreach ($traversable as $key => $value) {
            if (is_iterable($value)) {
                $value = $this->apply($value);
                if (!$value && !is_array($value)) {
                    continue;
                }
            } elseif ($value === \luc\UNIT) {
                continue;
            }
            $result[$key] = $value;
        }
        return $result;
    }
}