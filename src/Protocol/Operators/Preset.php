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

    public function makeMapping(string $alias, string $pattern): Preset\Mapping {
        $mapping = new Preset\Mapping($pattern, $this);
        $this->_mappings[$alias] = $mapping;
        return $mapping;
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
            array_key_exists($key, $slice) && $this->_arguments[$position] = $slice[$key];
        }
        return $this;
    }

    public function __toString(): string {
        return "$this->_name,".implode(',', $this->_arguments);
    }

    private function getMapping(string $name): Preset\Mapping {
        if (!isset($this->_mappings[$name])) {
            throw new \UnexpectedValueException("Preset $this->_name is not defined.");
        }
        if (is_array($this->_mappings[$name])) {
            $conf    = $this->_mappings[$name];
            $mapping = $this
                ->makeMapping($name, $conf[0])
                    ->setDefaults(...$conf[1])
                    ->setSlicePosition(...$conf[2]);

        }
        return $this->_mappings[$name];
    }
}