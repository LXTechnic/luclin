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

    public function handle(Request $request, Closure $next)
    {
        $xheaders = \luc\xheaders();
        $headers  = $request->headers->all();

        // 先处理queryable的参数
        foreach ($this->queryable as $name => $header) {
            if (!($value = $request->input(static::$queryPrefix."$name"))) {
                continue;
            }
            // 这里的处理方式不会覆盖原headers中的数据
            $headers[$header][] = $value;
        }

        // 再从头中读取
        foreach ($this->prefixes as $prefix => $limit) {
            foreach ($headers as $header => $values) {
                if (!$limit) {
                    break;
                }
                if (strpos($header, $prefix) !== 0) {
                    continue;
                }
                $limit--;

                $name = substr($header, strlen($prefix)) ?: $prefix;
                isset($this->mapping[$name]) && $name = $this->mapping[$name];

                $value = (isset($values[1]) && in_array($name, $this->arrayable))
                    ? $values : $values[0];
                $xheaders->$name = $value;

                $xheaders->setRaw($header, $value);
            }
        }

        $xheaders->confirm();
        return $next($request);
    }

}