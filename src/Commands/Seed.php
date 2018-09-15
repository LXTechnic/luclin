<?php

namespace Luclin\Commands;

use Luclin\Module;

use Illuminate\Console\Command;
use File;

class Seed extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:seed {module?} {--classes=} {--force}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '执行某个或所有模块的seed';

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
        [
            'module'    => $module,
        ] = $this->arguments();
        @[
            'classes'   => $classes,
            'force'     => $force,
        ] = $this->options();
        if ($classes) {
            $classIndex = [];
            foreach (explode(',', $classes) as $class) {
                $classIndex[ucfirst($class)] = true;
            }
        } else {
            $classIndex = null;
        }

        if ($module) {
            $modules = [$module];
        } else {
            $modules = array_keys(Module::initedModules());
        }
        foreach ($modules as $module) {
            $this->info(">> seed by module [$module]");
            $directory = \luc\mod($module)->path('database', 'seeds');
            if (file_exists($directory)) foreach (File::allFiles($directory)
                as $info)
            {
                $name = $info->getBasename('.php');
                if (substr($name, strlen($name) - 6) != 'Seeder') {
                    continue;
                }
                if ($classIndex && !isset($classIndex[substr($name, 0, strlen($name) - 6)])) {
                    continue;
                }
                $params = [
                    '--class'   => "Seeds\\$name",
                ];
                $force  && $params['--force'] = $force;
                $this->call('db:seed', $params);
            }
        }

    }

}
