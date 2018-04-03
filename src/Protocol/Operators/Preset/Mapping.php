<?php

namespace Luclin\Protocol\Operators\Preset;

use Luclin\Protocol\Operators\Preset;
use Luclin\Meta\Collection;

class Mapping extends Collection
    implements \Luclin\MetaInterface
{
    protected $_pattern;

    protected $_defaults = [];

    protected $_slicePosition = [];

    protected $_arguments = [];

    public function __construct(string $pattern) {
        $this->_pattern = $pattern;
    }

    public function setArguments(array $arguments): self {
        foreach ($this->_defaults as $key => $default) {
            (!isset($arguments[$key]) || $arguments[$key] == '')
                && $arguments[$key] = $default;
        }
        $this->_arguments = $arguments;
        return $this;
    }

    public function parse(): array {
        parse_str($this->apply(), $result);
        return $result;
    }

    public function setDefaults(...$defaults): self {
        $this->_defaults = $defaults;
        return $this;
    }

    public function getDefault($position) {
        return $this->_defaults[$position] ?? null;
    }

    public function setSlicePosition(int $limitPosition,
        int $offsetPosition = null, int $modePosition = null): self
    {
        $offsetPosition === null && $offsetPosition = $limitPosition + 1;
        $this->_slicePosition = [
            --$limitPosition,
            --$offsetPosition,
            $modePosition,
        ];
        return $this;
    }

    public function getSlicePosition(): array {
        return $this->_slicePosition;
    }

    public function __toString(): string {
        return $this->apply();
    }

    private function apply(): ?string {
        return sprintf($this->_pattern, ...$this->_arguments);
    }
}