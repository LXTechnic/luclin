<?php

namespace Luclin\Commands\Baseline;

use Luclin\Support\Baseline;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Console\Command;
use File;

class Snap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:baseline.snap {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '根据Snap配置更新模块版本';

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
        $topConfFile = base_path('.baseline.yml');
        try {
            if (file_exists($topConfFile)) {
                $topConf = Yaml::parse(file_get_contents($topConfFile));
            } else {
                $topConf = [];
            }
        } catch (\Throwable $exc) {
            throw $exc;
        }

        $baseline = new Baseline($topConf);
        foreach ($baseline->applySnap($this->argument('name'))
            as [$dir, $url, $tag])
        {
            $baseline->checkout($dir, $url, $tag);
        }
        $this->info('done.');
    }

}