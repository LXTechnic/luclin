<?php

namespace Luclin\Commands\Module;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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
        $this->info('done.');
    }

}
