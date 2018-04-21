<?php

namespace Luclin\Providers;

use Luclin\Loader;
use Luclin\Support;
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
    Facades\Queue,
    ServiceProvider
};
use Illuminate\Database\Eloquent;

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
        Queue::before(function (JobProcessing $event) {
            Support\CacheLoader::cleanAll();
        });
        $this->app->resolving(function ($object, $app) {
            if ($object instanceof Request) {
                $object->confirm();
            }
        });

        $this->declareMacros();

        Loader::instance('operator')->register('Luclin\\Protocol\\Operators');

        $this->app->runningInConsole() && function_exists('luc')
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

        $this->app->bind(Contracts\Uri\FragmentPlug::class,
            Uri\Plugs\FragmentSlice::class);
    }

    protected function bindPaths()
    {
        foreach ([
            'luclin.path' => $root = dirname(dirname(__DIR__)),
        ] as $abstract => $instance) {
            $this->app->instance($abstract, $instance);
        }
    }

    protected function declareMacros() {
        Eloquent\Collection::macro('pluckCustom',
            function(string $field, callable $func, ...$arguments)
        {
            $result = [];
            foreach ($this as $item) {
                $plucked = $func($item->$field, ...$arguments);
                if (!$plucked) {
                    continue;
                }

                if (is_array($plucked)) {
                    array_push($result, ...$plucked);
                } else {
                    $result[] = $plucked;
                }
            }
            return $result;
        });
    }

}
