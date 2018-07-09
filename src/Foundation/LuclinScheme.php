<?php

namespace Luclin\Foundation;

use Luclin\Luri\Scheme;
use Luclin\Routers;

class LuclinScheme extends Scheme
{
    protected static function _routers(): array {
        return parent::_routers() + [
            'preset'    => Routers\Preset::class,
            'query'     => Routers\Query::class,
            'seek'      => Routers\Seek::class,
        ];
    }

    public function construct() {

    }
}