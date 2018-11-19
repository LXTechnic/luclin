<?php

namespace Luclin\Support\Mqt;

class Packet
{
    public $version = 1;

    private $_id;
    private $context = [];
    private $body;

    public function __construct(array $body) {
        $this->body     = $body;
        $this->_id      = \luc\idgen::sorted36();
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
        $id      = array_shift($body);
        $context = array_pop($body);
        $packet  = new static($body);
        $packet->version = $version;
        $packet->context = $context;
        $packet->_id = $id;
        return $packet;
    }

    public function pack(): string {
        $body = $this->body;
        array_unshift($body, $this->_id);
        array_unshift($body, $this->version);
        $body[] = $this->context ?: null;
        return msgpack_pack($body);
    }
}