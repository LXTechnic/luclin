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
        $env = $this->argument('env');

        $root   = base_path();
        $source = $root.\DIRECTORY_SEPARATOR.'.environments'.
            \DIRECTORY_SEPARATOR."$env.conf";
        $target = $root.\DIRECTORY_SEPARATOR.".env";
        File::delete($target);
        File::copy($source, $target);

        // 测试环环境准备
        $this->prepareEnvTesting($root);

        // TODO: 回头再实现整合多modules中的env

        $this->info("set env to [$env]");
    }

    private function prepareEnvTesting(string $root): void {
        $source = $root.\DIRECTORY_SEPARATOR.'.environments'.
            \DIRECTORY_SEPARATOR."testing.conf";
        $target = $root.\DIRECTORY_SEPARATOR.".env.testing";
        if (!File::exists($target) && File::exists($source)) {
            File::copy($source, $target);
        }
    }
}
