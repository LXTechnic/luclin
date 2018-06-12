<?php

namespace Luclin\Support;

use Firebase\JWT\JWT;
use Log;

class JwtToken
{
    private $payload = [
        'sub'   => 0,
        'iss'   => '',
        'iat'   => 0,
        'exp'   => 0,
        'nbf'   => 0,
        'jti'   => 0,
        'ext'   => [],
    ];

    private $duration;
    private $secret;
    private $idGenerator;
    private $userLoader = null;

    public function __construct(string $secret, callable $generator, int $duration) {
        $this->secret       = $secret;
        $this->idGenerator  = $generator;
        $this->duration     = $duration;
    }

    public function setUserLoader(callable $loader): self {
        $this->userLoader = $loader;
        return $this;
    }

    public function setExtra(array $extra): self {
        $this->payload['ext'] = $extra;
        return $this;
    }

    public function setAuth($auth): self {
        $this->payload['sub'] = $auth;
        return $this;
    }

    public function setFrom(string $url): self {
        $this->payload['iss'] = $url;
        return $this;
    }

    public function setCreatedAt(int $time): self {
        $this->payload['iat'] = $time;
        return $this;
    }

    public function setExpireTime(int $time): self {
        $this->payload['exp'] = $time;
        return $this;
    }

    public function setActivedAt(int $time): self {
        $this->payload['nbf'] = $time;
        return $this;
    }

    public function make(): array {
        !$this->payload['iat'] && $this->payload['iat'] = time() - 10;
        !$this->payload['exp'] && $this->payload['exp'] = $this->payload['iat'] + $this->duration;
        !$this->payload['nbf'] && $this->payload['nbf'] = $this->payload['iat'];

        $generator = $this->idGenerator;
        $this->payload['jti'] = $generator();

        // Log::info('make jwt token', $this->payload);

        return [$this->payload['jti'], JWT::encode($this->payload, $this->secret), $this->payload];
    }

    public function extra() {
        return $this->payload['ext'] ?? null;
    }

    public function getExpireTime(): int {
        return $this->payload['exp'];
    }

    public function parse(string $token): ?self {
        try {
            $this->payload = (array)JWT::decode($token, $this->secret, ['HS256']);
            // Log::info('parse jwt token', $this->payload);
        } catch (\Throwable $e) {
            return null;
        }
        return $this;
    }

    public function user() {
        if ($this->userLoader) {
            $loader = $this->userLoader;
            return $loader($this->payload);
        }
        return $this->payload['sub'];
    }

    public function validate(array $credentials = []) {

    }
}