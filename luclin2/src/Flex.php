<?php

namespace Luclin2;

abstract class Flex {
    use Features\Pipable;

    protected $items = [];

    public function __construct(...$items) {
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

    public function items(): array {
        return $this->items;
    }

    public function map(): iterable {
        foreach ($this->items as $key => $item) {

        }

        return $this->items;
    }
}
