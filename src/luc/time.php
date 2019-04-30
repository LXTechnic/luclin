<?php

namespace luc;

use Illuminate\Support\Carbon;

class time
{
    static $mocks = [];

    public static function create($time): Carbon {
        return is_int($time) ? Carbon::createFromTimestamp($time) :
            new Carbon($time, config('app.timezone'));
    }

    public static function mock($time = null, string $name = 'default'): void {
        if ($time) {
            self::$mocks[$name] = is_string($time) ? self::create($time) : $time;
        } else {
            unset(self::$mocks[$name]);
        }
    }

    public static function now(?string $name = 'default'): Carbon {
        if ($name && isset(self::$mocks[$name])) {
            return self::create(self::$mocks[$name]);
        }
        return now();
    }
}