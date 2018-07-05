<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property int $target_type
 * @property int $target_id
 */
trait HasTarget
{
    protected static function migrateUpHasTarget(Blueprint $table): void
    {
        $table->string('target_type', 50)->nullable();
        $table->string('target_id', 250)->nullable();
    }

    protected static function migrateDownHasTarget(Blueprint $table): void
    {
        $table->dropColumn('target_type');
        $table->dropColumn('target_id');
    }
}