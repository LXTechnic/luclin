<?php

namespace Luclin\Commands\Deploy;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use File;


class Cache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:deploy:cache {--clear}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '建立发布缓存';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Perform the operation.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->option('clear')) {
            $this->call('config:clear');
            $this->call('route:clear');
            return;
        }

        $this->call('config:cache');
        $this->call('route:cache');
    }

}
