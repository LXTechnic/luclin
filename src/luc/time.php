<?php

namespace luc;

use Illuminate\Support\Carbon;

class time
{
    public static function create($time): Carbon {
        return new Carbon($time, config('app.timezone'));
    }

}