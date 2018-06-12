<?php

namespace Luclin\Protocol\Operators;

use Luclin\Meta\Collection;
use Luclin\Contracts;
use Luclin\Protocol\Operator;
use Luclin\Protocol\Foundation;

class Preset extends Collection
    implements \Luclin\MetaInterface, Contracts\Operator
{
    use Foundation\OperableTrait;

    protected $_name;

    protected $_arguments;

    protected $_mappings;

    protected $_current;

    public function __construct(string $value, array $mappings = []) {
        $this->_arguments   = explode(',', $value);
        $this->_name        = array_shift($this->_arguments);
        $this->_mappings     = $mappings;
    }

    public function name(): string {
        return $this->_name;
    }

    public function setMapping(string $name,
        string $pattern, ?callable $maker = null): self
    {
        $this->_mappings[$name] = [$pattern, $maker];
        return $this;
    }

    public function currentMapping(): Preset\Mapping {
        return $this->_current;
    }

    public function confirm(): \Luclin\MetaInterface {
        parent::confirm();

        $mapping = $this->getMapping($this->_name);
        $this->_current = $mapping;
        $this->fill($this->_current->setArguments($this->_arguments)->parse());
        return $this;
    }

    public function getSlice(): array {
        [
            $limitPosition,
            $offsetPosition,
            $modePosition,
        ] = $this->_current->getSlicePosition();
        return [
            $this->getArgument($limitPosition),
            $this->getArgument($offsetPosition),
            $this->getArgument($modePosition),
        ];
    }

    public function getArgument($position) {
        if (!isset($this->_arguments[$position]) || $this->_arguments[$position] == '')
        {
            return $this->_current->getDefault($position);
        }
        return $this->_arguments[$position];
    }

    public function setSlice(...$slice): self {
        foreach ($this->_current->getSlicePosition() as $key => $position) {
            if ($position === null) {
                continue;
            }
            $slice[$key] !== null && array_key_exists($key, $slice)
                && $this->_arguments[$position] = $slice[$key];
        }
        return $this;
    }

    public function __toString(): string {
        $argumentPosition = 0;
        $arguments = [];
        foreach ($this->_arguments as $pos => $value) {
            if ($argumentPosition < $pos) {
                for ($i = $argumentPosition; $i < $pos; $i++) {
                    $arguments[$i] = null;
                }
            }
            $arguments[$pos]    = $value;
            $argumentPosition   = $pos + 1;
        }
        return "$this->_name,".implode(',', $arguments);
    }

    private function getMapping(string $name): Preset\Mapping {
        if (!isset($this->_mappings[$name])) {
            throw new \UnexpectedValueException("Preset $this->_name is not defined.");
        }
        if (!($this->_mappings[$name] instanceof Preset\Mapping)) {
            [$pattern, $maker]  = $this->_mappings[$name];
            $mapping = new Preset\Mapping($pattern);
            $maker && $maker($mapping);
            $this->_mappings[$name] = $mapping;

        }
        return $this->_mappings[$name];
    }
}