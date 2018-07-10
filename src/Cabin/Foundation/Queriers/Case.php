<?php

namespace Luclin\Cabin\Foundation\Seekers;

use Luclin\Context;

class Case implements Contracts\Endpoint
{
    public function __invoke($id, array $params = [], Context $context = null)
    {
        // $query = $context['query']
        return 123;
    }
}