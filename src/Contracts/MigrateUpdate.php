<?php

namespace Luclin\Contracts;

use Illuminate\Database\Schema\Blueprint;

interface MigrateUpdate
{
    public static function migrateUpdate(Blueprint $table, ...$features): void;
}