<?php

namespace Luclin\Commands\Baseline;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Console\Command;
use File;

class Trail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lu:baseline.trail {name} {ver?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据Trail配置更新模块版本';

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
        $this->info('done.');
    }

}
