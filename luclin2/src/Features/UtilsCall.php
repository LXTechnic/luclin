<?php

namespace Luclin2\Features;

use Luclin2\Utils;

trait UtilsCall {
    public function __call(string $name, array $arguments) {
        $utils = new Utils();
        return $utils->$name($this, ...$arguments);
    }
}
