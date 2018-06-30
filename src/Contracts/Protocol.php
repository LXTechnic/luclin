<?php

namespace Luclin\Contracts;

use Luclin\Abort;
use Luclin\Protocol\Response;

interface Protocol
{
    public function abort(Abort $abort, ?Response $response = null): Response;
}