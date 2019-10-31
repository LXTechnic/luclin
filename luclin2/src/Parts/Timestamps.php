<?php

namespace Luclin2\Parts;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $created_at
 * @property string $updated_at
 * @property string $deleted_at
 */
trait Timestamps
{
    use SoftDeletes;

    protected static function migrateUpTimestamps(Blueprint $table): void
    {
        $table->timestamps();
        $table->softDeletes();
    }

    protected static function migrateDownTimestamps(Blueprint $table): void
    {
        $table->dropColumn('created_at');
        $table->dropColumn('updated_at');
        $table->dropColumn('deleted_at');
    }
}