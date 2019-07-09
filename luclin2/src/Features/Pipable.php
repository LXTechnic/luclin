<?php

namespace Luclin2\Features;

trait Pipable {
    public function pipe($primary = null): Luclin2\Pipe {
        return new Luclin2\Pipe($primary, $this);
    }
}
