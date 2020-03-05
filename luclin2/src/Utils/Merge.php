<?php

namespace Luclin2\Utils;

class Merge
{
    protected $origin;
    protected $merges;

    public function __construct(array $origin, ...$merges)
    {
        $this->origin   = $origin;
        $this->merges   = $merges;
    }

    public function __invoke(): array {
        return array_merge($this->origin, ...$this->merges);
    }
}