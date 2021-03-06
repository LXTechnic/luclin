<?php

namespace Luclin\Support\Mqt;


/**
 */
class Packet
{
    public $version = '1.0';

    private $_id;
    private $context = [];
    private $fact;

    public function __construct(array $fact = null) {
        if ($fact) {
            $this->fact     = $fact;
            $this->_id      = \luc\idgen::sorted36();
        }
    }

    public function setContext(array $context): self {
        return $this;
    }

    public function getFact(): array {
        return $this->fact;
    }

    public function getContext(): array {
        return $this->context;
    }

    public static function unpack(string $payload): ?self {
        $payload = msgpack_unpack($payload);
        if (!$payload || ($payload[0] ?? null) != 'PHOIAC') {
            return null;
        }

        $packet  = new static();
        [
            $sub,
            $packet->version,
            $packet->_id,
            $status,
            $count,
            $section, // 目前写死一个
        ] = $payload;

        [
            $sectionStatus,
            $context, // 写死context
            $packet->fact, // 写死一个
        ] = $section;

        $packet->context = $context ?: [];
        return $packet;
    }

    public function pack(): string {
        // 1 << 7 表示包括context
        // 若& 1 << 6 则表示简化，简化时每个fact的query会采用数组表示
        $section = [1 << 7, $this->context ?: null, $this->fact];
        // 这里的packet开关配置为0，预留
        // 1 表示写死一个section
        $payload = ['PHOIAC', $this->version, $this->_id, 0, 1, $section];
        return msgpack_pack($payload);
    }
}