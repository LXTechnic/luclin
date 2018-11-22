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
        $this->setContract('code', 201);
        return $this->send(201, $headers);
    }

    public function wait(?string $operation = null, $headers = []) {
        $operation && $headers['Operation-Location'] = $operation;
        $this->setContract('code', 202);
        return $this->send(202, $headers);
    }

    public function iHaveNotIdea($headers = []) {
        $this->setContract('code', 203);
        return $this->send(203, $headers);
    }

    public function ok(bool $refresh = false, $headers = []) {
        $code = $refresh ? 205 : 204;
        $this->setContract('code', $code);
        return $this->send($code, $headers);
    }

    public function image(string $data, string $format = 'png') {
        return response($data, 200, [
            'content-type'  => "image/$format",
        ]);
    }
}
