<?php

namespace Luclin2\Utils;

class Pick
{
    protected $data;
    protected $config;
    protected $default;

    public function __construct(iterable $data, array $config, $default = null)
    {
        $this->data     = $data;
        $this->config   = $config;
        $this->default  = $default;
    }

    public function __invoke(): array {
        $result = [];
        foreach ($this->config as $key) {
            $result[$key] = $this->data[$key] ??
                (is_array($this->default) ? $this->default[$key] : $this->default);
        }
        return $result;
    }
}