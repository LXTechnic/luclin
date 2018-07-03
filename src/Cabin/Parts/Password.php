<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property string $name
 */
trait Password
{
    protected static function migrateUpPassword(Blueprint $table,
        bool $nullable = false): void
    {
        $f = $table->string('password', 250)->nullable();
    }

    protected static function migrateDownPassword(Blueprint $table): void
    {
        $table->dropColumn('password');
    }
}