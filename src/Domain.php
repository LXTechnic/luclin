<?php

namespace Luclin;

use Luclin\Contracts;

/**
 */
abstract class Domain extends Meta\Collection
{
    use Foundation\SingletonTrait;

    public function getRoleClass(string $role): string {
        return static::class.'\\'.ucfirst($role).'Role';
    }
}
