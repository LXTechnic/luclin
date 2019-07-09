<?php

namespace Luclin2\Features;

use Luclin2\Utils;

trait Pipable {
    public function pipe($primary = null): Utils\Pipe {
        return new Utils\Pipe($primary, $this);
    }
}
