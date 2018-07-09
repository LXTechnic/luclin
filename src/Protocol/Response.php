<?php

namespace Luclin\Protocol;

use Luclin\Meta\Collection;

class Response extends Container
{

    public static function ok(...$arguments) {
        $data = ['ok' => 1];
        $arguments && $data['extra'] = $arguments;
        return response($data);
    }

    public function send($code = 200, $headers = []) {
        return response($this->confirm()->toArray(), $code, $headers);
    }

}
