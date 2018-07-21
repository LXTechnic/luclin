<?php

namespace Luclin\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:make.test {module} {name} {--unit}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为某个模块创建测试项';

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
            'unit'      => $unit,
        ] = $this->options();

        dd(\luc\mod($module));
        $path = \luc\mod($module)->path($path);
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new \Exception("Migations directory [$path] is not exists.");
            }
        }

        $tmpName    = Str::random();
        $params     = [
            'name'  => $tmpName,
        ];
        $unit   && $params['--unit']    = $name;
        $this->call('make:test', $params);
    }

}
