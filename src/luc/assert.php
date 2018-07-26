<?php

namespace luc;

class assert
{
    public static function exc($exc): void {
        if ($exc instanceof \Throwable) {
            static::dumpException($exc);
        } elseif (isset($exc->exception) && $exc->exception instanceof \Throwable) {
            static::dumpException($exc->exception);
        }
    }

    protected static function dumpException(\Throwable $exc): void {
        echo "\n!!> Exception raise: ";
        echo $exc->getMessage();
        echo " @ ".$exc->getFile()."(".$exc->getLine().")";
        echo "\n".$exc->getTraceAsString()."\n";
    }

}