<?php

namespace Luclin;

use Luclin\Loader;
use Luclin\Support\{
    Command
};

use Illuminate\Support\{
    ServiceProvider as LaravelServiceProvider
};

abstract class ServiceProvider extends LaravelServiceProvider
{
    protected static $moduleName = 'unamed';

    protected $loaders = [];

    public function boot()
    {
        parent::boot();

        foreach ($this->loaders as $name => $space) {
            Loader::instance($name)->register($space);
        }
    }

    protected function getModuleKey(): string {
        return "lumod:".static::$moduleName;
    }

    protected function makeModule(string $root): Module {
        if (!($module = $this->app->get($this->getModuleKey()))) {
            $module = new Module(static::$moduleName, $root);
            $this->app->instance($this->getModuleKey(), $module);
        }
        return $module;
    }

    protected function module(): ?Module {
        return $this->app->get($this->getModuleKey());
    }

    protected function registerCommandsByPath(string $space, string $path): self {
        Command::register($space, $path);
        return $this;
    }
}