<?php

namespace Luclin2\Features;

/**
 * TODO: 这个方法暂时不靠谱，需要改进
 */
trait Pipable {
    public function pipe($primary = null): \Luclin2\Pipe {
        return new \Luclin2\Pipe($primary, $this);
    }
}
