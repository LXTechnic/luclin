<?php

namespace Luclin\Flow;

use Luclin\Contracts;
use Luclin\Meta;

class Context extends Meta\Collection implements Contracts\Context
{
    public function cleanRoles(): self {
        foreach ($this as $item) {
            if (is_object($item) && method_exists($item, 'cleanRoles')) {
                $item->cleanRoles();
            }
        }
        return $this;
    }
}
