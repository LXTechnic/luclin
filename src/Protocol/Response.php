<?php

namespace Luclin\Protocol;

use Luclin\Meta\Collection;
use Luclin\Uri;

class Response extends Container
{

    public static function ok() {
        return response(['ok' => 1]);
    }

    public static function msg(string $key, array $aliases = []) {
        return response([
            '_msg' => new Types\Message(static::translate($key, $aliases)),
        ]);
    }

    protected static function translate(string $key, array $aliases = []): string {
        return __("messages.responses.$key", $aliases);
    }

    public function send() {
        return response($this->confirm()->toArray());
    }

}
