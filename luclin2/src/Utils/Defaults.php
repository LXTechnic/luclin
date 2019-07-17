<?php

namespace Luclin2\Utils;

class Defaults
{
    protected $data;
    protected $defaults;

    public function __construct(iterable $data, array $defaults)
    {
        $this->data     = $data;
        $this->defaults = $defaults;
    }

    public function __invoke(): array {
        $result = [];
        foreach ($this->defaults as $name => $merges) {
            if (is_array($merges)) {
                $default = array_shift($merges);
                [$hook]  = \luc\tail($merges);
                if (is_callable($hook)) {
                    array_pop($merges);
                } else {
                    $hook = null;
                }
            } else {
                if (is_callable($merges)) {
                    $default = null;
                    $hook    = $merges;
                } else {
                    $default = $merges;
                    $hook    = null;
                }
                $merges  = [];
            }

            !isset($this->data[$name]) && $this->data[$name] = \luc\UNIT;
            $value = in_array($this->data[$name], $merges, true) ?
                $default : $this->data[$name];
            $result[$name] = $hook ? $hook($value) : $value;
        }

        return \luc\_($result)->united()();
    }
}