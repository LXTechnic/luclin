<?php

namespace Luclin\Commands\Deploy;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Console\Command;
use File;

class Env extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:deploy.env {env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '设置当前运行环境';

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
        // dd(Yaml::parse(file_get_contents(base_path('lumod.yml'))));
        $this->info('done.');
    }

}
