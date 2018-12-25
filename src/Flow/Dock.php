<?php

namespace Luclin\Flow;

use Luclin\Contracts;
use Luclin\Flow;
use Luclin\Foundation;

/**
 */
abstract class Dock
{
    protected static $domains   = [];

    protected static $flows     = null;

    public static $context      = null;

    abstract protected function flows(): array;

    public static function __callStatic(string $name, array $arguments) {
        if (!static::$flows) {
            $dock = new static();
            static::$flows = $dock->flows();
        }

        $flow = static::$flows[$name] ?? null;
        if (!$flow) {
            throw new \BadFunctionCallException("Flow $name@".static::class." is not exists.");
        }

        // 这里重新布局了参数结构, 首为context, domains位于context中约定 _domains字段表述
        $context = array_shift($arguments);
        if (isset($context['_domains'])) {
            $domains = $context['_domains'];
            !is_array($domains) && $domains = [$domains];
        } else {
            $domains = static::$domains;
        }
        [$result, $content] = $flow($domains, $context, ...$arguments);
        return $result;
    }
}
