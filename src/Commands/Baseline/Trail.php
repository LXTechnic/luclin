<?php

namespace Luclin\Commands\Baseline;

use Luclin\Support\Baseline;

use Illuminate\Console\Command;
use File;

class Trail extends Command
{
    use CommonTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:baseline:trail {ver} {--conf=?}';

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
        $baseline = new Baseline($this->conf());
        foreach ($baseline->applyTrail($this->argument('ver'))
            as [$dir, $url, $flag])
        {
            $baseline->checkout($dir, $url, $flag);
        }
        $this->info('done.');
    }

}
