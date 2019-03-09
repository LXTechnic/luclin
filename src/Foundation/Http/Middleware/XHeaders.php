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
    protected $inherits = [];

    protected $arrayable    = [];
    protected $queryable    = [];
    protected $jsonObject   = [];

    public function handle(Request $request, Closure $next)
    {
        $xheaders = \luc\xheaders(true);
        $headers  = $request->headers->all();

        // 先处理queryable的参数
        foreach ($this->queryable as $name => $header) {
            if (!($value = $request->input(static::$queryPrefix."$name"))) {
                continue;
            }
            // 这里的处理方式不会覆盖原headers中的数据
            isset($headers[$header]) ? array_unshift($headers[$header], $value) :
                ($headers[$header][] = $value);
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

        // 处理jsonObject
        if ($this->jsonObject) foreach ($this->jsonObject as $name) {
            $value = $xheaders->$name;
            !is_array($value) && ($xheaders->$name = json_decode($value, true) ?: []);
        }

        // 处理继承
        if ($this->inherits)  foreach ($this->inherits as $to => $from) {
            !$xheaders->$to && $xheaders->$to = $xheaders->$from;
        }

        $xheaders->confirm();
        return $next($request);
    }

}