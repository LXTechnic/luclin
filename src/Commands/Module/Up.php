<?php

namespace Luclin\Commands\Module;

use Luclin\Support\Composer\Fork\JsonFormatter;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Console\Command;
use File;

/**
 * update for modules
 */
class Up extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:module.up {directories?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '指定模块目录更新并安装模块';

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
        $baseComposerConf = file_get_contents(base_path('composer.json'));
        $baseComposerConf = json_decode($baseComposerConf, true);

        $directories = $this->argument('directories') ?: [base_path('modules')];
        $requires    = [];
        foreach ($directories as $dir) {
            foreach (File::allFiles($dir) as $file) {
                if ($file->getBasename() != 'composer.json') {
                    continue;
                }
                $modulePath     = $file->getPath();
                $content        = file_get_contents($file->getRealPath());
                $composerConf   = json_decode($content, true);
                $baseComposerConf['repositories'][$composerConf['name']] = [
                    'type'  => 'path',
                    'url'   => $modulePath,
                ];
                $baseComposerConf['require'][$composerConf['name']] = '*';
                $requires[] = $composerConf['name'];
            }
        }
        $newComposerConf = JsonFormatter::format(json_encode($baseComposerConf));
        file_put_contents(base_path('composer.json'), $newComposerConf);

        foreach ($requires as $packageName) {
            exec("composer require $packageName");
        }

        $this->call('package:discover');
        $this->call('vendor:publish', [
            '--tag'     => 'public',
            '--force'   => true,
        ]);

        $this->info('done.');
    }

}
