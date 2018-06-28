<?php

namespace Luclin\Foundation\Auth;

use Luclin\Meta;
use Luclin\Foundation\AuthTrait;

use Illuminate\Contracts\Auth\Authenticatable;

class Guest extends Meta\Struct implements Authenticatable
{
    use AuthTrait;

    protected static function _defaults(): array {
        return parent::_defaults() + [
            'id'        => '',
            'name'      => '',
            'anonymous' => true,
        ];
    }

    public function model() {
        return null;
    }
}
