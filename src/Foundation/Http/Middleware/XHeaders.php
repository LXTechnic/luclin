<?php

namespace Luclin\Foundation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Contracts\Config\Repository;

class XHeaders
{
    protected static $queryPrefix = '__';

    protected $prefixes = [];
    protected $mapping  = [];

    protected $arrayable = [];
    protected $queryable = [];

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function handle(Request $request, Closure $next)
    {
        $xheaders = \luc\ins('xheaders');

        // 先处理queryable的参数
        foreach ($this->queryable as $name => $alias) {
            if (!($value = $request->input(static::$queryPrefix."$alias"))) {
                continue;
            }
            is_numeric($name) && $name = $alias;
            $xheaders->$name = $value;
        }

        // 再从头中读取
        foreach ($this->prefixes as $prefix => $limit) {
            foreach ($request->headers as $name => $values) {
                if (!$limit) {
                    break;
                }
                if (strpos($name, $prefix) !== 0) {
                    continue;
                }
                $limit--;

                $name = substr($name, strlen($prefix)) ?: $prefix;
                isset($this->mapping[$name]) && $name = $this->mapping[$name];

                $xheaders->$name = (isset($values[1]) && in_array($name, $this->arrayable))
                    ? $values : $values[0];
            }
        }

        $xheaders->confirm();
        return $next($request);
    }

}