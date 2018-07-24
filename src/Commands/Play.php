<?php

namespace Luclin\Commands;

use Illuminate\Console\Command;

class Play extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:play';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        \luc\timer();
        for ($i = 0; $i < 100000; $i++) {
            \luc\idgen::sortedUuid();
        }
        $this->info(\luc\timer());
    }

}
