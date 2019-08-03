<?php

namespace Luclin\Support;

class Letter
{
    private static $mapping = [];
    private static $mappingFlipped = [];

    public static function encode($number): string {
        is_int($number) ? ($number = dechex($number)) : ($number = strtolower($number));
        $number    = gmp_strval(gmp_init("0x$number", 16), 26);
        $result = '';

        $mapping = static::mapping();
        for ($i = 0; $i < strlen($number); $i++) {
            $result .= $mapping[$number[$i]];
        }
        return $result;
    }

    public static function decode(string $letter): string {
        $letter = strtoupper($letter);
        $mapping = static::mapping(true);
        $tmp    = '';
        for ($i = 0; $i < strlen($letter); $i++) {
            $tmp .= $mapping[$letter[$i]];
        }
        return gmp_strval(gmp_init($tmp, 26), 16);
    }

    public static function mapping(bool $flip = false): array {
        static
            $to      = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
            $from    = '0123456789abcdefghijklmnop';

        if (!static::$mapping) {
            for ($i = 0; $i < strlen($to); $i++) {
                static::$mapping[$from[$i]] = $to[$i];
            }
        }

        if ($flip) {
            if (!static::$mappingFlipped) {
                static::$mappingFlipped = array_flip(static::$mapping);
            }

            $mapping = static::$mappingFlipped;
        } else {
            $mapping = static::$mapping;
        }

        return $mapping;
    }
}
