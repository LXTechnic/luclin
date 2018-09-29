<?php

namespace Luclin\Foundation\Providers;

use Luclin\Loader;
use Luclin\Module;
use Luclin\Support\{
    Command
};

use Illuminate\Support\{
    ServiceProvider
};
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

abstract class AppService extends ServiceProvider
{
    protected static $moduleName;
    protected static $moduleSpace;

    protected static $loaders = [];

    protected $modulePaths = [];

    abstract protected function initModule(): void;

    /**
     * Boorstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        foreach (static::$loaders as $name => $spaces) {
            Loader::instance($name)->register(...$spaces);
        }

        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerTranslations();
        $this->registerViews();
        $this->registerCommands();
        $this->registerFactories();
        // 注册多态关联模型映射id
        $this->registerMorphMap();

        $this->publishAssets();

        $this->registerResolving();
        $this->declareMacros();
        $this->registerQueueJobEvent();
    }

    protected function registerResolving(): void {
    }

    protected function declareMacros(): void {
    }

    protected function registerQueueJobEvent(): void {
    }

    protected function registerRoutes(): void {
        if (isset($this->modulePaths['routes']))
            foreach ($this->modulePaths['routes'] as $file)
                $this->loadRoutesFrom($file);
    }

    protected function registerMigrations(): void {
        if (isset($this->modulePaths['migrations']))
            foreach ($this->modulePaths['migrations'] as $dir)
                $this->loadMigrationsFrom($dir);
    }

    protected function registerTranslations(): void {
        if (isset($this->modulePaths['lang']))
            foreach ($this->modulePaths['lang'] as $dir => $name)
                $this->loadTranslationsFrom($dir, $name);
    }

    protected function registerViews(): void {
        if (isset($this->modulePaths['views']))
            foreach ($this->modulePaths['views'] as $dir => $name)
                $this->loadViewsFrom($dir, $name);
    }

    protected function registerCommands(): void {
        if ($this->app->runningInConsole() && isset($this->modulePaths['commands']))
            foreach ($this->modulePaths['commands'] as $namespace => $dir)
                $this->registerCommandsByPath($namespace, $dir);
    }

    protected function registerFactories(): void {
        $factory = app(EloquentFactory::class);
        if ($this->app->runningInConsole()) {
            $factory->load($this->module()->path('database', 'factories'));
        }
    }

    protected function registerMorphMap(): void
    {
        // Relation::morphMap([
        //     'posts'     => 'App\Post',
        //     'videos'    => 'App\Video',
        // ]);
    }

    protected function publishAssets(): void {
        if (isset($this->modulePaths['assets']))
            $this->publishes($this->modulePaths['assets'], 'public');
    }

    public function register()
    {
        $this->initModule();
        // Paths load
        $this->modulePaths = (include $this->module()->path('src', 'paths.php'))
            ->call($this);

        $this->importConfig();
        $this->bindSingletions();
    }

    protected function importConfig(): void {
        if (isset($this->modulePaths['config']))
            foreach ($this->modulePaths['config'] as $path => $name)
                $this->importConfigFrom($path, $name);
    }

    protected function bindSingletions(): void {
        // $this->app->singleton(static::$moduleName.':services.pass', function () {
        //     return new Pass();
        // });
    }

    protected function getModuleKey(): string {
        return "lumod:".static::$moduleName;
    }

    protected function makeModule(string $root): Module {
        if (!$this->app->has($this->getModuleKey())) {
            $module = new Module(static::$moduleName, static::$moduleSpace, $root);
            $this->app->instance($this->getModuleKey(), $module);
        }
        return $this->app->get($this->getModuleKey());
    }

    protected function module(): ?Module {
        return $this->app->get($this->getModuleKey());
    }

    protected function registerCommandsByPath(string $space, string $path): self {
        Command::register($space, $path);
        return $this;
    }

    protected function importConfigFrom(string $path, $key): void {
        $config = $this->app->config->get($key, []);

        env('APP_ENV') && $this->app->config->set($key, array_merge($config, require $path));
    }
}