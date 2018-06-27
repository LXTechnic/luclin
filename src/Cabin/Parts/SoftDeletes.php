<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes as EloquentSoftDeletes;

/**
 * @property string $deleted_at
 */
trait SoftDeletes
{
    use EloquentSoftDeletes;

    protected static function migrateUpSoftDeletes(Blueprint $table): void
    {
        $table->softDeletes();
    }

    protected static function migrateDownSoftDeletes(Blueprint $table): void
    {
        $table->dropColumn('deleted_at');
    }
}