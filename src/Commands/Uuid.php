<?php

namespace Luclin\Commands;

use Luclin\Module;

use Illuminate\Console\Command;
use File;

class Uuid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:uuid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成一串可排序的唯一id';

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
        $this->info("Generated: ".\luc\idgen::sortedUuid());
    }

}
