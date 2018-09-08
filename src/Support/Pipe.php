<?php

namespace Luclin\Support;

use Luclin\Contracts;

class Pipe
{
    private $handle;
    private $agent;
    private $processes = [];

    /**
     *
     * @param mixed $handle
     * @param string|Contracts\CallAgent $agent
     */
    public function __construct($handle, $agent = '') {
        $this->handle   = $handle;
        $this->agent    = $agent;
    }

    public function __call(string $name, array $arguments) {
        $this->processes[] = [$name, $arguments];
        return $this;
    }

    public function __get(string $name) {
        return $this->$name();
    }

    public function __invoke() {
        $target = $this->handle;
        foreach ($this->processes as [$func, $arguments]) {
            if (strpos($func, '_') === 0) {
                switch ($func) {
                    case '_closure':
                        $func   = array_shift($arguments);
                        $target = $func($target, ...$arguments);
                        break;
                }
                continue;
            }

            if ($this->agent instanceof Contracts\CallAgent) {
                $target = $this->agent->$func($target, ...$arguments);
            } else {
                $func = "$this->agent\\$func";
                $target = $func($target, ...$arguments);
            }
        }
        return $target;
    }
}
