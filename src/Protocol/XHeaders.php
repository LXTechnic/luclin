<?php

namespace Luclin\Protocol;

use Luclin\Meta;

class XHeaders extends Meta\Struct
{
    protected static function _defaults(): array {
        return parent::_defaults() + config('protocol.xheaders');
    }

}