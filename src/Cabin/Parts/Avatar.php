<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property string $name
 */
trait Avatar
{
    protected static function migrateUpAvatar(Blueprint $table,
        bool $nullable = false): void
    {
        $f = $table->string('avatar', 250)->nullable();
    }

    protected static function migrateDownAvatar(Blueprint $table): void
    {
        $table->dropColumn('avatar');
    }
}