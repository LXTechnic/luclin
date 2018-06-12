<?php

namespace Luclin\Flow;

class Freeze
{
    protected $domainClass;

    protected $context;

    protected $flow;

    public function __construct(\Luclin\Flow $flow,
        string $domainClass, array $context)
    {
        $this->flow         = $flow;
        $this->domainClass  = $domainClass;
        $this->context      = $context;
    }

    public function __invoke() {
        $flow = $this->flow;
        $domainClass = $this->domainClass;
        return $flow($domainClass::instance(), $this->context);
    }
}
