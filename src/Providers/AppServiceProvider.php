<?php

namespace Luclin\Providers;

use Luclin\Support;
use Luclin\Contracts;
use Luclin\Cabin;
use Luclin\Loader;
use Luclin\Luri;
use Luclin\Routers;
use Luclin\Foundation\{
    Providers,
    LuclinScheme
};
use Luclin\Protocol\{
    XHeaders,
    Operator,
    Request
};

use Illuminate\Database\Eloquent;
// use Illuminate\Database\Eloquent\{
//     Relations\Relation as Relation
// };
use Illuminate\Auth\AuthenticationException;
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
    protected static $moduleName    = 'luclin';
    protected static $moduleSpace   = 'Luclin';

    protected static $loaders = [
        'querier'   => [
            'Luclin\\Cabin\\Foundation\\Queriers',
        ],
        'seeker'   => [
            'Luclin\\Cabin\\Foundation\\Seekers',
        ],
    ];

    /**
     * Boorstrap the service provider.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Luri::registerScheme('luc', LuclinScheme::instance());

        Operator::register('preset',    'luc:preset/');
        Operator::register('query',     'luc:query/');
        Operator::register('seek',      'luc:seek/');
    }

    protected function registerResolving(): void {
        $this->app->resolving(Request::class, function ($request, $app) {
            $request->confirm();
        });
    }

    protected function declareMacros(): void {
        Eloquent\Collection::macro('sortWithIds',
            function(array $ids, string $idField = 'id'): Eloquent\Collection
        {
            $result = new static();
            $keyed  = $this->keyBy($idField);
            foreach ($ids as $id) {
                $result[] = $keyed[$id] ?? null;
            }
            return $result;
        });

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
            Cabin::clean();
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

        $this->registerContextContracts();
        $this->registerSingleton();
    }

    protected function registerContextContracts() {
        // inner service 相关约定的注入
        $this->app->bind('context._auth', function($app) {
            try {
                return $app->make('Luclin\Contracts\Auth');
            } catch (AuthenticationException $exc) {
                // do nothing..
            }
            return null;
        });
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
