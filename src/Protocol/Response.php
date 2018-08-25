<?php

namespace Luclin\Protocol;

use Luclin\Meta\Collection;

/**
 * 206和207等未封装
 */
class Response extends Container
{

    public function send($code = 200, $headers = []) {
        return response($this->confirm()->toArray(), $code, $headers);
    }

    public function created(?string $resource = null, $headers = []) {
        $resource && $headers['Location'] = $resource;
        return $this->send(201, $headers);
    }

    public function wait(?string $operation = null, $headers = []) {
        $operation && $headers['Operation-Location'] = $operation;
        return $this->send(202, $headers);
    }

    public function iHaveNotIdea($headers = []) {
        return $this->send(203, $headers);
    }

    public function ok(bool $refresh = false, $headers = []) {
        return $this->send($refresh ? 205 : 204, $headers);
    }
}
