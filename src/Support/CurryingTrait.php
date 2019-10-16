<?php

namespace Luclin\Support;

use Luclin\Contracts;

trait CurryingTrait
{
    private $_lastCurrying;

    public function for(...$arguments): object {
        return $this->_makeCurrying($arguments);
    }

    public function forBack(...$arguments): object {
        if (!$this->_lastCurrying) {
            throw new \Exception('No previous currying exists.');
        }

        $previousArgs   = $this->_lastCurrying->arguments();
        $count          = count($arguments);
        if ($count < count($previousArgs)) {
            $previousArgs   = array_slice($previousArgs, 0, -$count);
            $arguments      = array_merge($previousArgs, $arguments);
        }
        return $this->_makeCurrying($arguments);
    }

    protected function _makeCurrying(array $arguments): object {
        $this->_lastCurrying = new class($this, ...$arguments) {
            private $agent;
            private $arguments;

            public function __construct($agent, ...$arguments)
            {
                $this->agent        = $agent;
                $this->arguments    = $arguments;
            }

            public function __call(string $name, array $arguments) {
                if ($name == 'forBack') {
                    $this->arguments = array_slice($this->arguments, 0,
                        count($arguments));
                }

                return $this->agent->$name(...array_merge($this->arguments,
                    $arguments));
            }

            public function arguments(): array {
                return $this->arguments;
            }
        };
        return $this->_lastCurrying;
    }
}
