<?php

namespace Luclin\Commands\Module;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Console\Command;
use File;

class Update extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'module:update';

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
        dd(Yaml::parse(file_get_content(base_path('lumod.yml'))));
        $this->info('done.');
    }

}
