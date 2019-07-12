<?php

namespace luc {

    use Luclin2;

    function flex(...$arguments): Luclin2\Flex {
        return new Luclin2\Flex(...$arguments);
    }

    function context(...$arguments): Luclin2\Context {
        return new Luclin2\Context(...$arguments);
    }

    function _($primary): Luclin2\Pipe {
        return new Luclin2\Pipe($primary, new Luclin2\Utils());
    }
}

