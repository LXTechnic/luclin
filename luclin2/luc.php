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

    function head(iterable $data, $noTail = false): array {
        if ($noTail) {
            $head = $data[0];
            $tail = null;
        } else {
            $head = array_shift($data);
            $tail = $data;
        }
        return [$head, $tail];
    }

    function tail(iterable $data, $noHead = true): array {
        if ($noHead) {
            $tail = $data[count($data) - 1];
            $head = null;
        } else {
            $tail = array_pop($data);
            $head = $data;
        }
        return [$tail, $head];
    }

    // 待独立后放开
    // const UNIT = ':unit';

}

