<?php

namespace Luclin2;

class Flex implements \ArrayAccess, \Countable, \JsonSerializable, \IteratorAggregate
{
    use Features\Pipable;

    protected $items = [];

    protected $previous;

    public function __construct(...$items) {
        $this->assign($items);
    }

    public function assign($items): void {
        $this->previous = $this->items;

        if (count($items) == 1) {
            $items = $items[0];
            if (is_array($items)) {
                $this->items = $items;
            } elseif (is_iterable($items)) {
                foreach ($items as $key => $item) {
                    $this->items[$key] = $item;
                }
            } else {
                $this->items[] = $items;
            }
        } else {
            $this->items = $items;
        }
    }

    public function __invoke(): self {
        $this->resolve();
        return $this;
    }

    public function resolve($mode = null) {
        $result     = [];
        $keys       = [];
        $params     = [];
        $resolver   = [];
        foreach ($this->it() as $key => $value) {
            if (is_callable($value)) {
                $result[] = $value->call($this, $params, $keys);
            } else {
                $params[]   = $value;
                $keys[]     = $key;
            }
        }
        $this->assign($result);
    }

    public function pop() {
        return array_pop($this->items);
    }

    public function shift() {
        return array_shift($this->items);
    }

    public function items(): iterable {
        return $this->items;
    }

    public function it(bool $reverse = false): iterable {
        $items = $reverse ? \array_reverse($this->items) : $this->items;
        foreach ($items as $key => $item) {
            yield $key => $item;
        }

        return $items;
    }

    public function exists($key): bool {
        return array_key_exists($key, $this->items);
    }

    public function drop($key): void {
        unset($this->items[$key]);
    }

    public function get($key, $default = null) {
        return $this->items[$key] ?? $default;
    }

    public function set($value, $key = null): void {
        $key === null ? ($this->items[] = $value) : ($this->items[$key] = $value);
    }

    public function count(): int {
        return count($this->items);
    }

    public function offsetExists($key): bool
    {
        return $this->drop($key);
    }

    public function offsetGet($key)
    {
        return $this->get($key);
    }

    public function offsetSet($key, $value): void
    {
        $this->set($value, $key);
    }

    public function offsetUnset($key)
    {
        $this->drop($key);
    }

    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof \JsonSerializable) {
                return $value->jsonSerialize();
            } elseif (is_object($value) && method_exists($value, 'toArray')) {
                return $value->toArray();
            }

            return $value;
        }, $this->items);
    }

    public function getIterator(): iterable {
        return $this->it();
    }
}
