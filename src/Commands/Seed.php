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
    protected $signature = 'luc:seed {module?} {--class} {--force}';

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
            'class'     => $class,
            'force'     => $force,
        ] = $this->options();

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
                $params = [
                    '--class'   => "Seeds\\$name",
                ];
                $force  && $params['--force'] = $force;
                $this->call('db:seed', $params);
            }
        }

    }

}
