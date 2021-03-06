<?php

namespace Luclin;

abstract class Protocol implements Contracts\Protocol
{
    public function response(): Protocol\Response {
        return new Protocol\Response();
    }

    public function ok(...$arguments) {
        $response = $this->response();
        $arguments && $response->extra = $arguments;
        return $response->ok();
    }

    public function abort(Abort $abort,
        ?Protocol\Response $response = null): Protocol\Response
    {
        !$response && $response = new Protocol\Response();

        $name = $abort->noticeOnly ? 'notice' : 'error';
        return $response->addContract($name, $this->makeAbort($abort));
    }

    public function makeAbort(Abort $abort): array {
        return [
            'message'   => $abort->getMessage(),
            'code'      => $abort->getCode(),
            'extra'     => $abort->extra()['_show'] ?? (new class{}),
            'hidden'    => $abort->extra()['_hidden'] ?? false,
            '$jump'     => $abort->extra()['_jump'] ?? [],
        ];
    }
}
