<?php

namespace Luclin\Foundation\Domains\Common;

use Luclin\Flow\Role;

class DumperRole extends Role
{
    public function dump(): void {
        dump($this->raw());
    }

}