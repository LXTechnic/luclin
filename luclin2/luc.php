<?php

class luc {

    public static function flex(...$arguments): Luclin2\Flex {
        return new Luclin2/Flex(...$arguments);
    }

    public static function context(...$arguments): Luclin2\Context {
        return new Luclin2/Context(...$arguments);
    }

}

