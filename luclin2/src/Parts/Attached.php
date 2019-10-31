<?php

namespace Luclin2\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property string $super_id
 * @property string $master_id
 */
trait Attached
{
    protected static function migrateUpAttached(Blueprint $table): void
    {
        $table->string('super_id', 250)->nullable()->comment('顶层所属关联');
        $table->string('master_id', 250)->nullable()->comment('上级所属关联');
    }

    protected static function migrateDownAttached(Blueprint $table): void
    {
        $table->dropColumn('super_id');
        $table->dropColumn('master_id');
    }
}