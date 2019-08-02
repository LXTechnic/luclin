<?php

namespace Luclin\Cabin\Patterns;

use Luclin\Cabin;
use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class Log extends Cabin\Model\Generated2
    implements Contracts\MigrateUpdate
{
    use SoftDeletes;

    protected static $unguarded = true;

    protected $casts = [
    ];

    public static function migrateUpdate(Blueprint $table, ...$features): void {
        static::migrateUp($table,
            'Primary'
        );
        $table->timestamps();
        $table->softDeletes();

    }
}