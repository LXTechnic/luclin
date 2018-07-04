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

    protected function getPasswordSecret(): string {
        return config('app.key');
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password']
            = password_hash($this->getPasswordSecret().$value, \PASSWORD_BCRYPT);
    }

    public function verifyPassword(string $value): bool {
        return password_verify($this->getPasswordSecret().$value, $this->password);
    }
}