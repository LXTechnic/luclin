<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property string $name
 */
trait Named
{
    protected static function migrateUpNamed(Blueprint $table,
        bool $nullable = false): void
    {
        $f = $table->string('name', 50);
        $nullable && $f->nullable();
    }

    protected static function migrateDownNamed(Blueprint $table): void
    {
        $table->dropColumn('name');
    }
}