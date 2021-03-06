<?php

namespace luc;

use Luclin\Support;

class idgen
{
    /**
     * 生成可排序的36进制字串id
     *
     * @param integer $length 最大值为16，超出后可能会出现浮点记数的报错
     * @return string
     */
    public static function sorted36(int $length = 10, int $multiple = 2): string {
        $gen = new Support\IdGenerator();
        for ($i = 0; $i < $multiple; $i++) {
            $gen->randByLength($length);
        }
        return $gen->orderable()
            ->gmpStrval(36)
            ->get();
    }

    public static function sorted62(int $length = 10, int $multiple = 2): string {
        $gen = new Support\IdGenerator();
        for ($i = 0; $i < $multiple; $i++) {
            $gen->randByLength($length);
        }
        return $gen->orderable()
            ->gmpStrval(62)
            ->get();
    }

    public static function transBit(string $id,
        int $fromBit = 62, int $toBit = 36): string
    {
        $gmp  = gmp_init($id, $fromBit);
        return gmp_strval($gmp, $toBit);
    }

    public static function sortedUuid(int $bit = 36): string {
        return (new Support\IdGenerator())
            ->orderable()
            ->gmpStrval($bit)
            ->get().
            (new Support\IdGenerator())
            ->uuid()
            ->gmpStrval($bit, '0x')
            ->get();
    }
}

