<?php

namespace luc;

use Illuminate\Support\Arr;

class assert
{
    public static function exc($exc): void {
        if ($exc instanceof \Throwable) {
            static::dumpException($exc);
        } elseif (isset($exc->exception) && $exc->exception instanceof \Throwable) {
            static::dumpException($exc->exception);
        }
    }

    public static function arrayHas(array $arr, string $key) {
        $value = Arr::get($arr, $key);
        if ($value == null) {
            throw new \Exception("Assert array key [$key] fail.");
        }
        return $value;
    }

    protected static function dumpException(\Throwable $exc): void {
        echo "\n!!> Exception raise: ";
        echo $exc->getMessage();
        echo " @ ".$exc->getFile()."(".$exc->getLine().")";
        echo "\n".$exc->getTraceAsString()."\n";
    }

}