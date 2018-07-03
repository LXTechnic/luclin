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
        $table->smallInteger('target_type')->nullable();
        $table->bigInteger('target_id')->nullable();
    }

    protected static function migrateDownHasTarget(Blueprint $table): void
    {
        $table->dropColumn('target_type');
        $table->dropColumn('target_id');
    }
}