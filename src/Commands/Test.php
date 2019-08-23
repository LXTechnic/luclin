<?php

namespace Luclin\Commands;

use Illuminate\Console\Command;
use File;
use Artisan;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:test {module?} {class?} {--seq=} {--exclude=} {--dissEx}';

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

        [
            'seq'       => $seq,
            'exclude'   => $exclude,
            'dissEx'    => $dissEx,
        ] = $this->options();

        if ($dissEx) {
            $exclude = [];
        } else {
            $exclude = $exclude ? explode(',', $exclude) : [];
            $exclude[] = 'luclin';
        }

        Artisan::call('config:clear', [
        ]);

        // $root   = \app_path().DIRECTORY_SEPARATOR."..";
        // $cmd    = realpath("$root/vendor/bin/phpunit").
        // $cmd    = "./vendor/bin/phpunit".
        //     " -c ".realpath("$root/phpunit.xml");
        $cmd    = "./vendor/bin/phpunit";

        // 整体多模块跑测试
        if (!$module) {
            if ($exclude) {
                // exclude仅在没有其他参数时生效
                foreach (\luc\mods() as $name => $_module) {
                    if (in_array($name, $exclude)) {
                        continue;
                    }

                    $this->info("--> Run tests for module [$name]");
                    $result = [];
                    exec("$cmd ".\luc\mod($name)->path('tests'), $result);
                    foreach ($result as $line) {
                        echo $line."\n";
                    }
                }
            } else {
                $result = [];
                exec("$cmd", $result);
                foreach ($result as $line) {
                    echo $line."\n";
                }
            }
            return;
        }

        // 按模块跑测试
        if (!$class) {
            $result = [];
            exec("$cmd ".\luc\mod($module)->path('tests'), $result);
            foreach ($result as $line) {
                echo $line."\n";
            }
            return;
        }

        // 单跑模块中的同名测试，支持指字序列
        $conf   = file_get_contents(\luc\mod($module)->path('composer.json'));
        $count  = 0;
        foreach (File::allFiles(\luc\mod($module)->path('tests')) as $info) {
            if (strtolower($class) != strtolower($info->getBasename('.php'))) {
                continue;
            }
            $count++;
            if ($seq && $seq != $count) {
                continue;
            }

            $result = [];
            exec("$cmd ".$info->getRealPath(), $result);
            foreach ($result as $line) {
                echo $line."\n";
            }
        }
    }

}
