<?php

namespace Luclin\Support;

use Luclin\Contracts;

class Pipe
{
    private $handle;
    private $agent;

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
        if ($this->agent instanceof Contracts\CallAgent) {
            $this->handle   = $this->agent->$name($this->handle, ...$arguments);
        } else {
            $func = "$this->agent\\$name";
            $this->handle   = $func($this->handle, ...$arguments);
        }
        return $this;
    }

    public function __get(string $name) {
        return $name == 'result' ? $this->handle : null;
    }
}
