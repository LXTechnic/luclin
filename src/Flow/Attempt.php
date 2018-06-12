<?php

namespace Luclin\Flow;

use Luclin\Flow;
use Luclin\Contracts;

class Attempt implements Contracts\Tickable
{
    protected $origin;
    protected $domainClass;
    protected $flowBody;
    protected $flowConfirm  = null;
    protected $flowRevert   = null;
    protected $flowCleanUp  = null;

    public function __construct(Contracts\Tickable $origin,
        ?string $domainClass, callable $way)
    {
        $this->origin       = $origin;
        $this->domainClass  = $domainClass;

        $this->flowBody  = $this->pretreatWay($way);
    }

    private function pretreatWay(callable $way): Flow {
        $flow = Flow::inherit($this->origin);
        $way($flow);
        return $flow;
    }

    public function confirm(callable $way = null): Flow {
        $way && $this->flowConfirm  = $this->pretreatWay($way);
        return $this->origin;
    }

    public function revert(callable $way = null): self {
        $way && $this->flowRevert   = $this->pretreatWay($way);
        return $this;
    }

    public function cleanUp(callable $way = null): self {
        $way && $this->flowCleanUp     = $this->pretreatWay($way);
        return $this;
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

        $body    = $this->flowBody;
        $confirm = $this->flowConfirm;
        $revert  = $this->flowRevert;
        $cleanUp = $this->flowCleanUp;
        $tick    = function() use ($body, $confirm, $revert, $cleanUp,
            $defaultDomainClass, $context)
        {
            $bodyResults = $revertResults = $cleanUpResults = $confirmResults =
                $results = [];

            if ($cleanUp) {
                try {
                    [$bodyResults] = $body($defaultDomainClass, $context);
                } catch (\Throwable $catch) {
                    $context->_catch = $catch;
                    if (!$revert) {
                        throw $catch;
                    }
                    [$revertResults] = $revert($defaultDomainClass, $context);
                } finally {
                    $cleanUp && [$cleanUpResults] = $cleanUp($defaultDomainClass, $context);
                    if (isset($catch)) {
                        // 出错了
                        return array_merge($bodyResults,
                            $revertResults, $cleanUpResults);
                    }
                }
            } else {
                try {
                    [$bodyResults] = $body($defaultDomainClass, $context);
                } catch (\Throwable $catch) {
                    $context->_catch = $catch;
                    if (!$revert) {
                        throw $catch;
                    }
                    [$revertResults] = $revert($defaultDomainClass, $context);
                }
            }
            [$confirmResults] = $confirm($defaultDomainClass, $context);

            return array_merge($bodyResults, $cleanUpResults, $confirmResults);
        };
        return [$tick, $this->origin];
    }
}
