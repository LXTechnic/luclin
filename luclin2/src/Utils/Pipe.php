<?php

namespace Luclin2\Utils;

class Pipe
{
    private $primary;
    private $handle;
    private $processes = [];

    /**
     *
     * @param mixed $primary
     * @param string|object $handle
     */
    public function __construct($primary, $handle = null) {
        $this->primary  = $primary;
        $this->handle   = $handle;
    }

    public function __call(string $name, array $arguments) {
        $this->processes[] = [$name, $arguments];
        return $this;
    }

    public function __get(string $name) {
        return $this->$name();
    }

    public function __invoke() {
        $primary = $this->primary;
        foreach ($this->processes as [$func, $arguments]) {
            if (strpos($func, '_') === 0) {
                switch ($func) {
                    case '_fn':
                        $func   = array_shift($arguments);
                        $primary = $func(...$this->params($primary, $arguments));
                        break;
                }
                continue;
            }

            if ($this->handle && is_object($this->handle)) {
                $primary = $this->handle->$func(...$this->params($primary, $arguments));
            } else {
                $func = "$this->handle\\$func";
                $primary = $func(...$this->params($primary, $arguments));
            }
        }
        return $primary;
    }

    private function params($primary, array $arguments): array {
        $primary !== null && array_unshift($arguments, $primary);
        return $arguments;
    }
}
