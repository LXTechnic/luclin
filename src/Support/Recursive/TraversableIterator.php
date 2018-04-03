<?php

namespace Luclin\Support\Recursive;

class TraversableIterator
{
    protected $root;
    protected $fun;

    public function __construct(iterable $traversable, ?callable $fun = null) {
        $this->root = $traversable;
        $this->fun  = $fun;
    }

    public function __invoke(): iterable {
        foreach ($this->loop($this->root) as $key => $value) {
            yield $key => $value;
        }
    }

    protected function loop($traversable): iterable {
        $fun = $this->fun;
        foreach ($traversable as $key => $value) {
            $signal = $fun($value, $key);
            if ($signal !== false
                && ($value instanceof \Traversable || is_array($value))) {
                foreach ($this->loop($value) as $nKey => $nValue ) {
                    yield $nKey => $nValue;
                }
            } else {
                yield $key => $value;
            }
        }
    }
}