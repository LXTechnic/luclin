<?php

namespace Luclin\Protocol;

use Luclin\Meta\Collection;

class Response extends Container
{

    public function send($code = 200, $headers = []) {
        return response($this->confirm()->toArray(), $code, $headers);
    }

}
