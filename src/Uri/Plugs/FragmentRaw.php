<?php

namespace Luclin\Uri\Plugs;

use Luclin\Contracts;

/**
 */
class FragmentRaw implements Contracts\Uri\FragmentPlug
{
    protected $parent;
    protected $value;

    public function __construct(string $value, \Luclin\Uri $parent) {
        $this->value    = $value;
        $this->parent   = $parent;
    }

    public function __invoke() {
        return $this->value;
    }

}
