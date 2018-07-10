<?php

namespace Luclin\Luri;

use Luclin\Contracts;

class Preset implements Contracts\Endpoint
{
    protected $name;
    protected $vars = [];
    protected $patterns;

    public function __construct(string $name, array $patterns = []) {
        $this->name     = $name;
        $this->patterns = $patterns;
    }

    public static function new(array $arguments, array $options,
        Contracts\Context $context): Contracts\Endpoint
    {
        $preset = new static($arguments[0], $context->patterns ?: []);
        $preset->assign($options);
        return $preset;
    }

    public function assign(array $vars): self {
        $this->vars = $vars;
        return $this;
    }

    public function setPattern(string $name, array $pattern): self {
        $this->patterns[$name] = $pattern;
        return $this;
    }
}