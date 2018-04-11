<?php

namespace Luclin\Providers;

use Luclin\Loader;
use Luclin\Contracts;
use Luclin\Uri;
use Luclin\Protocol\{
    Operators,
    Request
};
use Luclin\Support\{
    Command
};

use Illuminate\Support\{
    Facades\Redis,
    ServiceProvider
};

use Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Boorstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->resolving(function ($object, $app) {
            if ($object instanceof Request) {
                $object->confirm();
            }
        });

        Loader::instance('operator')->register('Luclin\\Protocol\\Operators');

        $this->app->runningInConsole()
            && Command::register('Luclin\\Commands', luc('path', 'src', 'Commands'));
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->bindPaths();

        $this->app->bind(Contracts\Uri\FragmentPlug::class, Uri\Plugs\FragmentSlice::class);
    }

    protected function bindPaths()
    {
        foreach ([
            'luclin.path' => $root = dirname(dirname(__DIR__)),
        ] as $abstract => $instance) {
            $this->app->instance($abstract, $instance);
        }
    }

}
