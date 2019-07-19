<?php

namespace Luclin2\Interaop;

class Request extends \Luclin2\Flex {

    private $raw;

    public function __construct(array $request = null) {
        $request && $this->assign($request);
    }

    public function setRaw(object $raw): void {
        $this->raw = $raw;
    }

    public function raw(): object {
        return $this->raw;
    }

    protected static function defaults(): array {
        return [];
    }

    protected static function validate(): array {
        return [];
    }

    public function resolve(...$tails) {
        $this[] = function(array $request) {
            return \luc\_($request)->defaults(static::defaults(), $this)();
        };
        parent::resolve(...$tails);
    }
}