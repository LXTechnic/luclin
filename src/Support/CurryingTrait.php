<?php

namespace Luclin\Support;

use Luclin\Contracts;

trait CurryingTrait
{
    private $_curry = null;

    public function for(...$arguments): object {
        $previous   = $this->_curry ? $this->_curry->arguments() : [];
        $appendArgs = $previous ?
            array_slice($arguments, count($previous)) : $arguments;

        if (is_callable($appendArgs[0] ?? null)) {
            $curry  = $this->_curry;
            $func   = array_shift($appendArgs);
            $func($this->_makeCurry($previous));

            $this->_curry = $curry;
            $arguments = $previous;
        }

        ($arguments || !$this->_curry) &&
            ($this->_curry = $this->_makeCurry($arguments));
        return $this->_curry;
    }

    private function _makeCurry(array $arguments): object {
        return new class($this, ...$arguments) {
            private $agent;
            private $arguments;

            public function __construct($agent, ...$arguments)
            {
                $this->agent        = $agent;
                $this->arguments    = $arguments;
            }

            public function __call(string $name, array $arguments) {
                return $this->agent->$name(...array_merge($this->arguments,
                    $arguments));
            }

            public function __get(string $name) {
                return $this->$name();
            }

            public function arguments(): array {
                return $this->arguments;
            }
        };
    }
}
