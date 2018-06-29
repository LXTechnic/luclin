<?php

namespace Luclin\Flow;

use Luclin\Flow;
use Luclin\Contracts;

class Match implements Contracts\Tickable
{
    protected $origin;
    protected $domainClass;
    protected $inspector;
    protected $flowSuccess;
    protected $flowFail = null;

    protected $lastResults = [];
    protected $again = false;

    public function __construct(Contracts\Tickable $origin,
        ?string $domainClass, $inspector, $success, $fail)
    {
        $this->origin       = $origin;
        $this->domainClass  = $domainClass;
        $this->inspector    = $inspector;

        $this->flowSuccess  = $this->pretreatWay($success);

        if ($fail) {
            $this->flowFail = $this->pretreatWay($fail);
        }
    }

    private function pretreatWay($way): Flow {
        $flow = Flow::inherit($this->origin);
        if (is_callable($way)) {
            $way($flow);
        } elseif ($way instanceof \Throwable) {
            $flow->raise($way);
        }
        return $flow;
    }

    public function lastResults(): array {
        return $this->lastResults;
    }

    public function again(): void {
        $this->again = true;
    }

    public function tick(string $defaultDomainClass,
        Flow\Context $context): array
    {
        $flow = null;
        if (!is_string($this->inspector)) {
            if ($this->inspector) {
                $tick = function() use ($defaultDomainClass, $context)
                {
                    $flow = $this->flowSuccess;
                    [$this->lastResults] = $flow($defaultDomainClass, $context);
                    return $this->lastResults;
                };
            } else {
                if ($this->flowFail) {
                    $tick = function() use ($defaultDomainClass, $context)
                    {
                        $flow = $this->flowFail;
                        [$this->lastResults] = $flow($defaultDomainClass, $context);
                        return $this->lastResults;
                    };
                } else {
                    $tick = null;
                }
            }
            return [$tick, $this->origin];
        }

        $domainClass = $this->domainClass ?: $defaultDomainClass;
        if ($domainClass::instance()->inspect($this->inspector, $context, $this))
        {
            $tick = function() use ($flow, $defaultDomainClass, $context)
            {
                $flow = $this->flowSuccess;
                [$this->lastResults] = $flow($defaultDomainClass, $context);
                $flow->revert();
                return $this->lastResults;
            };
        } else {
            if ($this->flowFail) {
                $tick = function() use ($defaultDomainClass, $context)
                {
                    $flow = $this->flowFail;
                    [$this->lastResults] = $flow($defaultDomainClass, $context);
                    $flow->revert();
                    return $this->lastResults;
                };
            } else {
                $tick = null;
            }
        }

        if ($this->again) {
            $this->again = false;
            $next = $this;
        } else {
            $next = $this->origin;
        }
        return [$tick, $next];
    }
}
