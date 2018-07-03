<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property int $amount
 */
trait Mobile
{
    protected static function migrateUpMobile(Blueprint $table): void
    {
        $table->string('mobile', 50)->nullable();
    }

    protected static function migrateDownMobile(Blueprint $table): void
    {
        $table->dropColumn('mobile');
    }
}