<?php

namespace Luclin2\Utils;

class Mapping
{
    protected $data;
    protected $config;

    public function __construct(iterable $data, array $config)
    {
        $this->data     = $data;
        $this->config   = $config;
    }

    public function __invoke(): array {
        $result = [];
        foreach ($this->data as $key => $value) {
            isset($this->config[$key]) && $key = $this->config[$key];
            $result[$key] = $value;
        }
        return $result;
    }
}