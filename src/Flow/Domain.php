<?php

namespace Luclin\Flow;

use Luclin\Contracts;
use Luclin\Flow;

use DB;
use Log;

/**
 */
abstract class Domain
{
    public function id(): string {
        return static::class;
    }

    public function hasFun($fun): bool {
        return method_exists($this, $fun);
    }

    public function role(string $name): ?object {
        $roleClass = $this->getRoleClass($name);
        if (!class_exists($roleClass)) {
            return null;
        }
        return new $roleClass();
    }

    public function __get(string $name): ?object {
        $providerClass = $this->getProviderClass($name);
        if (!class_exists($providerClass)) {
            return null;
        }
        return new $providerClass();
    }

    protected function getProviderClass(string $name): string {
        return static::class.'\\'.ucfirst($name).'Provider';
    }

    protected function getRoleClass(string $name): string {
        return static::class.'\\'.ucfirst($name).'Role';
    }

}
