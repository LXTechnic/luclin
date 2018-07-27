<?php

namespace Luclin\Commands\Make;

use Illuminate\Console\Command;

class Migration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:make:migration {module} {name} {--path=database/migrations} {--create=} {--table=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为某个模块创建迁移';

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
            'name'      => $name,
        ] = $this->arguments();
        @[
            'create'    => $create,
            'table'     => $table,
            'path'      => $path,
        ] = $this->options();

        $path = \luc\mod($module)->path($path);
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new \Exception("Migations directory [$path] is not exists.");
            }
        }

        $params = [
            'name'          => $name,
            '--path'        => $path,
            '--realpath'    => true,
        ];
        $create && $params['--create']  = $create;
        $table  && $params['--table']   = $table;
        $this->call('make:migration', $params);
    }

}
