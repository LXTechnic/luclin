<?php

namespace Luclin\Uri\Plugs;

use Luclin\Contracts;

/**
 */
class FragmentSlice implements Contracts\Uri\FragmentPlug
{
    protected $parent;
    protected $slice;

    public function __construct(string $value, \Luclin\Uri $parent) {
        $this->parent   = $parent;

        $slice = array_replace([
            20, 0, 'd'
        ], explode(',', $value));

        $slice[0] = max(1, min(100, intval(trim($slice[0])) ?: 20));
        $this->slice = $slice;
    }

    public function __invoke() {
        return $this->getSlice();
    }

    public function getSlice(): array {
        return $this->slice;
    }

    public function setSlice(...$slice): self {
        foreach ($slice as $key => $value) {
            $value !== null && $this->slice[$key] = $value;
        }
        return $this;
    }

    public function __toString(): string {
        return implode(',', $this->slice);
    }

    public function regress(): self {
        $this->parent->setFragment("$this");
        return $this;
    }

}
