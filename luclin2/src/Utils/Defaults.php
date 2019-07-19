<?php

namespace Luclin2\Utils;

class Defaults
{
    protected $data;
    protected $defaults;
    protected $handle;

    public function __construct(iterable $data, array $defaults, ?object $handle = null)
    {
        $this->data     = $data;
        $this->defaults = $defaults;
        $this->handle   = $handle;
    }

    public function __invoke(): array {
        $result = [];
        $hooks  = [];
        foreach ($this->defaults as $name => $merges) {
            if (is_array($merges)) {
                $default = array_shift($merges);
                [$hook]  = \luc\tail($merges);
                if (is_callable($hook)) {
                    array_pop($merges);
                    $hooks[$name] = $hook;
                }
            } else {
                if (is_callable($merges)) {
                    $default = null;
                    $hooks[$name] = $merges;
                } else {
                    $default = $merges;
                }
                $merges  = [];
            }

            !array_key_exists($name, $this->data) && $this->data[$name] = \luc\UNIT;
            $value = in_array($this->data[$name], $merges, true) ?
                $default : $this->data[$name];
            $result[$name] = $value;
        }

        $result = \luc\_($result)->united()();
        foreach ($result as $name => $val) {
            if (!isset($hooks[$name])) {
                continue;
            }
            $result[$name] = $this->handle ?
                $hooks[$name]->call($this->handle, $val, $result) :
                $hooks[$name]($val, $result);
        }
        return $result;
    }
}