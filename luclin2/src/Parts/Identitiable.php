<?php

namespace Luclin2\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property string $handle
 * @property string $mail
 * @property string $mobile
 * @property string $password
 */
trait Identitiable
{
    protected static function migrateUpIdentitiable(Blueprint $table): void
    {
        $table->string('handle', 250)->nullable()->comment('唯一名');
        $table->string('mail', 250)->nullable()->comment('电子邮件');
        $table->string('mobile', 250)->nullable()->comment('手机号');
        $table->string('password', 250)->nullable()->comment('密码');

        $table->unique(['handle']);
    }

    protected static function migrateDownIdentitiable(Blueprint $table): void
    {
        $table->dropColumn('handle');
        $table->dropColumn('mail');
        $table->dropColumn('mobile');
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