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
use Illuminate\Queue\{
    Events\JobProcessed,
    Events\JobProcessing,
    Events\JobFailed
};

use Log;

class AppServiceProvider extends \Luclin\ServiceProvider
{
    protected static $moduleName = 'luclin';

    protected $loaders = [
        'operator'  => 'Luclin\\Protocol\\Operators',
    ];

    /**
     * Boorstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerTranslations();
        $this->registerViews();
        $this->registerCommands();
        // 注册多态关联模型映射id
        $this->registerMorphMap();

        $this->publishAssets();

        $this->registerResolving();
        $this->declareMacros();
        $this->registerQueueJobEvent();
    }

    private function registerResolving(): void {
        $this->app->resolving(function ($object, $app) {
            if ($object instanceof Request) {
                $object->confirm();
            }
        });
    }

    private function declareMacros(): void {
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

    private function registerQueueJobEvent(): void {
        Queue::before(function (JobProcessing $event) {
            Support\CacheLoader::cleanAll();
        });
    }

    private function registerRoutes(): void {
        // $this->loadRoutesFrom($this->module()->path('routes.php'));
    }

    private function registerMigrations(): void {
        $this->loadMigrationsFrom($this->module()->path('database', 'migrations'));
    }

    private function registerTranslations(): void {
        $this->loadTranslationsFrom($this->module()->path('resources', 'lang'),
            static::$moduleName);
    }

    private function registerViews(): void {
        $this->loadViewsFrom($this->module()->path('resources', 'views'),
            static::$moduleName);
    }

    private function registerCommands(): void {
        if ($this->app->runningInConsole()) {
            $this->registerCommandsByPath('Luclin\\Commands',
                $this->module()->path('src', 'Commands'));
        }
    }

    private function registerMorphMap(): void
    {
        // Relation::morphMap([
        //     'posts'     => 'App\Post',
        //     'videos'    => 'App\Video',
        // ]);
    }

    private function publishAssets(): void {
        $this->publishes([
            $this->module()->path('assets')
                => public_path('vender/'.static::$moduleName),
        ], 'public');
    }

    public function register()
    {
        $this->initModule();
        $this->importConfig();
        $this->bindSingletions();

        $this->app->bind(Contracts\Uri\FragmentPlug::class,
            Uri\Plugs\FragmentSlice::class);
    }

    private function initModule(): void {
        $module = $this->makeModule(__DIR__.'/../..');

        // $module->setPathMapping([
        //     'tmp'   => '/tmp',
        // ]);
    }

    private function importConfig(): void {
        $this->mergeConfigFrom($this->module()->path('config', 'module.php'),
            static::$moduleName);

        $this->mergeConfigFrom($this->module()->path('config', 'aborts.php'),
            'aborts');
    }

    protected function bindSingletions(): void {
        // $this->app->singleton(static::$moduleName.':services.pass', function () {
        //     return new Pass();
        // });
    }

}
