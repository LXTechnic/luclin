<?php

namespace Luclin\Support;

/**
 * Description of IdGenerator
 *
 * @author andares
 */
class IdGenerator {
    /**
     * use hash_algos() get list.
     *
     * @var string
     */
    private $algo;

    private $raw;
    private $data;

    public function __construct(string $algo = 'tiger128,3') {
        $this->algo = $algo;
    }

    public function prepare(string $data = ''): self {
        $this->data     = $this->raw = $data;
        return $this;
    }

    public function get() {
        return $this->data;
    }

    public function md5(): self {
        $this->data = md5($this->data);
        return $this;
    }

    public function sha1(): self {
        $this->data = sha1($this->data);
        return $this;
    }

    public function hashHmac(string $secret = null, $returnRaw = false): self {
        $this->data = hash_hmac($this->algo, $this->data, $secret, $returnRaw);
        return $this;
    }

    public function randomHex(int $length = 8): self {
        $this->data = bin2hex(random_bytes($length));
        return $this;
    }

    public function rand(int $min, int $max): self {
        $this->data = mt_rand($min, $max);
        return $this;
    }

    public function randByLength(int $length): self {
        return $this->rand(10 ** $length, (10 ** ($length + 1)) - 1);
    }

    public function length(int $length): self {
        $this->data = substr($this->data, 0, $length);
        return $this;
    }

    public function orderable($precision = 100000): self {
        $this->data = intval(microtime(true) * $precision).$this->data;
        return $this;
    }

    public function to62(): self {
        static $dict = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        $to = 62;
        $ret = '';
        do {
            $result = $dict[bcmod($num, $to)].$result;
            $num = bcdiv($num, $to);
        } while ($num > 0);
        $this->data = $result;

        // TODO: 暂时保留一下老算法
        // $value  = $this->data;
        // $result = '';
        // do {
        //     // 精度问题
        //     $value < 10000 && $value = intval($value);

        //     $last    = $value % 62;
        //     $value   -= $last;
        //     $value && $value /= 62;

        //     $ord     = $last < 10 ? (48 + $last)
        //         : ($last > 35 ? (61 + $last) : (55 + $last));
        //     $result .= chr($ord);
        // } while ($value > 0);
        // $this->data = strrev($result);

        return $this;
    }

    public function gmpStrval($bit = 62, $prefix = ''): self {
        $data = $prefix.$this->data;
        $gmp  = gmp_init($data);
        $this->data = gmp_strval($gmp, $bit);
        return $this;
    }

    public function gmpIntval($bit = 62, $prefix = ''): self {
        $data = $prefix.$this->data;
        $gmp  = gmp_init($data, $bit);
        $this->data = gmp_intval($gmp);
        return $this;
    }

    public function base64(): self {
        $this->data = base64_encode($this->data);
        return $this;
    }

    public function urlencode(): self {
        $this->data = urlencode($this->data);
        return $this;
    }

    public function pack(string $pattern): self {
        $this->data = pack($pattern, $this->data);
        return $this;
    }

    public function strtoupper(): self {
        $this->data = strtoupper($this->data);
        return $this;
    }

    public function strtolower(): self {
        $this->data = strtolower($this->data);
        return $this;
    }

    public function baseConvert(int $from, int $to): self {
        $this->data = base_convert($this->data, $from, $to);
        return $this;
    }

    public function uuid($trim = true) {
        $id = uuid_create();
        $this->data = $trim ? str_replace('-', '', $id) : $id;
        return $this;
    }
}
