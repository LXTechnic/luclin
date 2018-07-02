<?php

namespace Luclin\Providers;

use Luclin\Support;
use Luclin\Contracts;
use Luclin\Foundation\Providers;
use Luclin\Uri;
use Luclin\Protocol\{
    XHeaders,
    Request
};

use Illuminate\Database\Eloquent;
// use Illuminate\Database\Eloquent\{
//     Relations\Relation as Relation
// };

use Illuminate\Support\{
    Facades\Queue
};
use Illuminate\Queue\{
    Events\JobProcessed,
    Events\JobProcessing,
    Events\JobFailed
};
use Auth;
use Log;

class AppServiceProvider extends Providers\AppService
{
    protected static $moduleName = 'luclin';

    protected static $loaders = [
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
    }

    protected function registerResolving(): void {
        $this->app->resolving(Request::class, function ($request, $app) {
            $request->confirm();
        });
    }

    protected function declareMacros(): void {
        Eloquent\Collection::macro('pluckCustom',
            function(string $field, callable $fun, ...$arguments)
        {
            $result = [];
            foreach ($this as $item) {
                $plucked = $fun($item->$field, ...$arguments);
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

    protected function registerQueueJobEvent(): void {
        Queue::before(function (JobProcessing $event) {
            Support\CacheLoader::cleanAll();
        });
    }

    public function register()
    {
        parent::register();

        // 实现控制器方法参数控制是否需要登录
        $this->app->bind('Luclin\Contracts\Auth', function($app) {
            return Auth::authenticate();
        });

        $this->registerSingleton();

        $this->app->bind(Contracts\Uri\FragmentPlug::class,
            Uri\Plugs\FragmentSlice::class);
    }

    protected function registerSingleton() {

        $this->app->singleton('luclin.xheaders', function ($app) {
            return new XHeaders();
        });
    }

    protected function initModule(): void {
        $module = $this->makeModule(__DIR__.'/../..');

        // $module->setPathMapping([
        //     'tmp'   => '/tmp',
        // ]);
    }

}
