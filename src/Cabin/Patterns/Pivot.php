<?php

namespace Luclin\Cabin\Patterns;

use Luclin\Cabin;
use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class Pivot extends Cabin\Model\Generated2
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

        // 关系
        $table->string('up_type', 250)->nullable()->comment('上游类型');
        $table->string('up_id', 250)->nullable()->comment('上游id');
        $table->string('down_type', 250)->nullable()->comment('下游类型');
        $table->string('down_id', 250)->nullable()->comment('下游id');

    }
}