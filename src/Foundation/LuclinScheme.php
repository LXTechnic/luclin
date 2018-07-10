<?php

namespace Luclin\Foundation;

use Luclin\Contracts\Context;
use Luclin\Luri\{
    Scheme,
    Preset
};
use Luclin\Cabin\Foundation as CabinRouter;

class LuclinScheme extends Scheme
{
    protected static function _nexts(): array {
        return parent::_nexts() + [
            'preset'    => Preset::class,
            'query'     => CabinRouter\Query::class,
            'seek'      => CabinRouter\Seek::class,
        ];
    }

    public function construct() {

    }
}