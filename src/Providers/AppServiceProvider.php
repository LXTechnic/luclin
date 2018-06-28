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
        $this->app->resolving(function ($object, $app) {
            if ($object instanceof Request) {
                $object->confirm();
            }
        });
    }

    protected function declareMacros(): void {
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

    protected function registerQueueJobEvent(): void {
        Queue::before(function (JobProcessing $event) {
            Support\CacheLoader::cleanAll();
        });
    }

    public function register()
    {
        parent::register();

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
