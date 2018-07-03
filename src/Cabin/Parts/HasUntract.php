<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property int $amount
 */
trait HasUntract
{
    protected static function migrateUpHasUntract(Blueprint $table): void
    {
        $table->string('untract', 250)->nullable();
    }

    protected static function migrateDownHasUntract(Blueprint $table): void
    {
        $table->dropColumn('untract');
    }
}