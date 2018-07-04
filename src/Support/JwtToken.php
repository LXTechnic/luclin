<?php

namespace Luclin\Support;

use Firebase\JWT\JWT;
use Log;

class JwtToken
{
    const MAPPING = [
        'auth'          => 'sub',
        'from'          => 'iss',
        'createdAt'     => 'iat',
        'expireTime'    => 'exp',
        'activedAt'     => 'nbf',
        'id'            => 'jti',
        'extra'         => 'ext',
    ];

    private $payload = [
        'sub'   => 0,
        'iss'   => '',
        'iat'   => 0,
        'exp'   => 0,
        'nbf'   => 0,
        'jti'   => 0,
        'ext'   => [],
    ];

    private $header = [];

    public function __construct(array $payload = null) {
        $payload && $this->payload = $payload;
    }

    public function head(array $header = null): array {
        $header && $this->header = $header;
        return $header;
    }

    public function make(string $secret, int $duration = 3600,
        $alg = 'HS256', $keyId = null): string
    {
        !$this->createdAt   && $this->createdAt     = now()->timestamp;
        !$this->activedAt   && $this->activedAt     = $this->createdAt - 10;
        !$this->expireTime  && $this->expireTime    = $this->createdAt + $duration;

        return JWT::encode($this->payload, $secret, $alg, $keyId, $this->header);
    }

    public static function parse(string $data, string $secret,
        array $allowedAlgs = ['HS256']): ?self
    {
        try {
            $token = new self((array)JWT::decode($data, $secret, $allowedAlgs));
        } catch (\Throwable $e) {
            return null;
        }
        return $token;
    }

    public function __get($name) {
        isset(self::MAPPING[$name]) && $name = self::MAPPING[$name];
        return $this->payload[$name];
    }

    public function __set($name, $value) {
        isset(self::MAPPING[$name]) && $name = self::MAPPING[$name];
        return $this->payload[$name] = $value;
    }

}