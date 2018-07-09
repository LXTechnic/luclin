<?php

namespace Luclin\Cabin\Foundation\Seekers;

class Start
{
    public function __invoke() {
        dump('start');
        return 123;
    }
}