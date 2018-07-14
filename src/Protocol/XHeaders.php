<?php

namespace Luclin\Protocol;

use Luclin\Meta;

class XHeaders extends Meta\Struct
{
    private $raw = [];

    protected static function _defaults(): array {
        return parent::_defaults() + config('protocol.xheaders');
    }

    public function setRaw(string $header, $value): self {
        $this->raw[$header] = $value;
        return $this;
    }

    public function raw(): array {
        return $this->raw;
    }
}