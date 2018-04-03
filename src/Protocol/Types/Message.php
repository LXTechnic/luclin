<?php

namespace Luclin\Protocol\Types;

use Luclin\Protocol\Type;

class Message extends Type
{
    protected static $_type = "Message";

    protected static function _defaults(): array {
        return [
            'message'   => '',
        ];
    }

    public function __construct(string $message) {
        $this->message = $message;
    }
}