<?php

namespace luc;

use Illuminate\Support\Carbon;

class time
{
    static $mocks = [];

    public static function create($time): Carbon {
        return new Carbon($time, config('app.timezone'));
    }

    public static function mock($time = null, string $name = 'default'): void {
        if ($time) {
            static::$mocks[$name] = is_string($time) ? static::create($time) : $time;
        } else {
            unset(static::$mocks[$name]);
        }
    }

    public static function now(?string $name = 'default'): Carbon {
        return $name ? (static::$mocks[$name] ?? now()) : now();
    }
}