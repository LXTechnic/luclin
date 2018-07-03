<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property string $created_at
 * @property string $updated_at
 */
trait Timestamps
{
    protected static function migrateUpTimestamps(Blueprint $table): void
    {
        $table->timestamps();
    }

    protected static function migrateDownTimestamps(Blueprint $table): void
    {
        $table->dropColumn('created_at');
        $table->dropColumn('updated_at');
    }
}