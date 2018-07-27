<?php

namespace Luclin\Foundation\Domains\Common;

use Luclin\Flow\Role;

class DumperRole extends Role
{
    public function du(): void {
        du($this->raw());
    }

}