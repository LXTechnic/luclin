<?php

namespace luc;

use Illuminate\Support\Arr;
use Log;

class assert extends \PHPUnit\Framework\Assert
{
    public static function checkExc($exc): void {
        if ($exc instanceof \Throwable) {
            static::dumpException($exc);
        } elseif (isset($exc->exception) && $exc->exception instanceof \Throwable) {
            static::dumpException($exc->exception);
        }
    }

    public static function hasExc($exc, $code = null): void {
        if ($exc instanceof \Throwable) {
            static::assertEquals($code, $exc->getCode());
        } elseif (isset($exc->exception) && $exc->exception instanceof \Throwable) {
            static::assertEquals($code, $exc->exception->getCode());
        } else {
            throw new \Exception('There has now exception throw out.');
        }
    }

    public static function raise(callable $func,
        $code = null, ?string $message = null): void
    {
        $hasExc = false;
        try {
            $func();
        } catch (\Throwable $exc) {
            $hasExc = true;
            if (class_exists($code)) {
                static::assertEquals($code, $exc);
            } else {
                $code    !== null && static::assertEquals($code,    $exc->getCode());
                $message !== null && static::assertEquals($message, $exc->getMessage());
            }
        }
        static::assertTrue($hasExc);
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
        echo "\n  See details in the log <!!\n\n";

        Log::debug("Test error: ".$exc->getMessage(), [
            'exception' => $exc,
        ]);
    }

}