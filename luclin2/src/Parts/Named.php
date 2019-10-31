<?php

namespace Luclin2\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property string $name
 * @property string $icon
 * @property string $title
 * @property string $description
 */
trait Named
{
    protected static function migrateUpNamed(Blueprint $table): void
    {
        $table->string('name', 250)->nullable()->comment('名称');
        $table->string('icon', 250)->nullable()->comment('图标');
        $table->string('title', 250)->nullable()->comment('标题');
        $table->text('description')->nullable()->comment('说明');
    }

    protected static function migrateDownNamed(Blueprint $table): void
    {
        $table->dropColumn('name');
        $table->dropColumn('icon');
        $table->dropColumn('title');
        $table->dropColumn('description');
    }
}