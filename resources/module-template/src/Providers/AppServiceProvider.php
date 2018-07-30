<?php

namespace :MyModule\Providers;

use Luclin\Foundation\Providers;

class AppServiceProvider extends Providers\AppService
{
    protected static $moduleName    = ':mymodule';
    protected static $moduleSpace   = ':MyModule';

    protected static $loaders = [
    ];

    public function boot()
    {
        parent::boot();
    }

    public function register()
    {
        parent::register();
    }

    protected function initModule(): void {
        $module = $this->makeModule(__DIR__.'/../..');
    }

}
