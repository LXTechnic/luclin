<?php

namespace Luclin\Commands\Make;

use Luclin\Module;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use File;

class Id extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:make:id';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个可排序uuid';

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
        $this->info(\luc\idgen::sortedUuid());
    }

}
