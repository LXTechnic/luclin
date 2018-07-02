<?php

namespace Luclin\Commands\ApiDoc;

use Symfony\Component\Yaml\Yaml;
use Illuminate\Console\Command;
use File;

class Gen extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:apidoc.gen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成api文档';

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
        $topConfFile = base_path('.apidoc.yml');
        try {
            if (file_exists($topConfFile)) {
                $topConf = Yaml::parse(file_get_contents($topConfFile));
            } else {
                $topConf = [];
            }
        } catch (\Throwable $exc) {
            throw $exc;
        }

        if (isset($topConf['scan'])) foreach ($topConf['scan'] as $key => $path) {
            $oa = \OpenApi\scan($path);
            echo $oa;
        }
    }

}
