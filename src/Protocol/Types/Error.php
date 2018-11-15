<?php

namespace Luclin\Protocol\Types;

class Error extends Message
{
    protected static $_type = "error";

    protected static function _defaults(): array {
        return parent::_defaults() + [
            'code'  => 0,
        ];
    }

    public function __construct(\Throwable $e = null) {
        $e && $this->code     = $e->getCode();
        $e && $this->message  = $e->getMessage();
    }
}