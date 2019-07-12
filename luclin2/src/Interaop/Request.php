<?php

namespace Luclin2\Interaop;

class Request extends \Luclin2\Flex {

    protected static $defaultResolveMode = self::RESOLVEMODE_RIGHT;

    public function __construct(array $request = null) {
        parent::__construct(function($request, $keys) {

        });

        if ($request) {
            $this[] = $request;
            $this();
        }
    }

    protected static function validate(): array {
        return [];
    }

}