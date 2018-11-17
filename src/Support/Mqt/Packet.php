<?php

namespace Luclin\Support\Mqt;

class Packet
{
    private $context = [];
    private $version;

    private $body;

    public function __construct(array $body, $version = 1) {
        $this->body     = $body;
        $this->version  = $version;
    }

    public function setContext(array $context): self {
        return $this;
    }

    public static function unpack(string $data): ?self {
        $body = msgpack_unpack($data);
        if (!$body) {
            return null;
        }

        $version = array_shift($body);
        $context = array_pop($body);
        $packet  = new static($body);
        $packet->version = $version;
        $packet->context = $context;
        return $packet;
    }

    public function pack(): string {
        $body = $this->body;
        array_unshift($body, $this->version);
        $body[] = $this->context ?: null;
        return msgpack_pack($body);
    }
}