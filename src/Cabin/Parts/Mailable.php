<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property string $name
 */
trait Mailable
{
    protected static function migrateUpMailable(Blueprint $table,
        bool $nullable = false): void
    {
        $table->string('email', 250)->unique()->nullable();
    }

    protected static function migrateDownMailable(Blueprint $table): void
    {
        $table->dropColumn('email');
    }
}