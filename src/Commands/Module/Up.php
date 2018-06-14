<?php

namespace Luclin\Commands\Module;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Console\Command;
use File;

class Up extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lu:module.up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新并安装模块';

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
        // if (!file_exists()) {

        // }
        dd(Yaml::parse(file_get_contents(base_path('lumod.yml'))));

        exec('composer update');

        $this->call('package:discover');
        $this->call('vendor:publish');
        $this->call('vendor:publish', [
            '--tag'     => 'public',
            '--force'   => true,
        ]);

        $this->info('done.');
    }

}
