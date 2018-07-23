<?php

namespace Luclin\Commands;

use Illuminate\Console\Command;

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

        $action = $this->mapping[$action];
        $params = [];
        $database   && $params['--database'] = $database;
        $module     && $path && $params['--path'] = \luc\mod($module)->path($path);
        $this->call('db:seed', $params);
    }

}