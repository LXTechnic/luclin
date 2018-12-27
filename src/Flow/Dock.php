<?php

namespace Luclin\Flow;

use Luclin\Contracts;
use Luclin\Flow;
use Luclin\Foundation;

/**
 * @see 因为闭包是以flow对象调用，因此在闭包内调自身方法不能使用static::而必须指定类名
 */
abstract class Dock
{
    protected static $domains   = [];

    public static $context      = null;

    abstract protected function flows(): array;

    public static function __callStatic(string $name, array $arguments) {
        $dock = new static();
        $flow = $dock->flows()[$name] ?? null;
        if (!$flow) {
            throw new \BadFunctionCallException("Flow $name@".static::class." is not exists.");
        }

        // 这里重新布局了参数结构, 首为context, domains位于context中约定 _domains字段表述
        $context = [];
        $domains = static::$domains;
        if ($arguments) {
            $context = array_shift($arguments);
            if (isset($context['_domains'])) {
                $domains = $context['_domains'];
                !is_array($domains) && $domains = [$domains];
            }
        }
        [$result, $content] = $flow($domains, $context, ...$arguments);
        return $result;
    }
}
