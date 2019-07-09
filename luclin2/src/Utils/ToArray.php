<?php

namespace Luclin2\Utils;

class ToArray
{
    protected $root;
    protected $fun;
    protected $nullable;

    public function __construct(iterable $traversable,
        ?callable $fun = null, ?array $nullable = null)
    {
        $this->root = $traversable;
        $this->fun  = $fun;
        $this->nullable = $nullable;
    }

    public function __invoke(): array {
        return $this->_toArray($this->root, $this->fun);
    }

    public function _toArray(iterable $traversable, callable $filter = null): array {
        $result = [];
        foreach ($traversable as $key => $value) {
            if (method_exists($value, 'toArray')) {
                $value = $value->toArray();
            } elseif ($value instanceof \Traversable || is_array($value)) {
                $value = $this->_toArray($value, $filter);
            }

            // 过滤器
            $filter && $value = $filter($value);

            // 过滤器支持跳过
            ($value !== null
                || $this->nullable === null
                || isset($this->nullable[$key]))
                    && $result[$key] = $value;
        }
        return $result;
    }
}