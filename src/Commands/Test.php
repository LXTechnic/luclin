<?php

namespace Luclin\Commands;

use Illuminate\Console\Command;
use File;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:test {module?} {class?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '执行模块内某个类名的测试';

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
        // 获取参数
        @[
            'module'    => $module,
            'class'     => $class,
        ] = $this->arguments();
        $class && $class = ucfirst($class).'Test';

        if (!$module) {
            exec("./vendor/bin/phpunit", $result);
            foreach ($result as $line) {
                $this->info($line);
            }
            return;
        }

        if (!$class) {
            exec("./vendor/bin/phpunit ".\luc\mod($module)->path('tests'), $result);
            foreach ($result as $line) {
                $this->info($line);
            }
            return;
        }

        $conf = file_get_contents(\luc\mod($module)->path('composer.json'));
        foreach (File::allFiles(\luc\mod($module)->path('tests')) as $info) {
            if ($class != $info->getBasename('.php')) {
                continue;
            }
            exec("./vendor/bin/phpunit ".$info->getRealPath(), $result);
            foreach ($result as $line) {
                $this->info($line);
            }
        }
    }

}
