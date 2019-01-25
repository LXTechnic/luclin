<?php

namespace Luclin\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    public function boot()
    {
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('db.connection', function ($app) {
            return $app['db']->connection();
        });
    }
}
