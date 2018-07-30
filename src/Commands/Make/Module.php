<?php

namespace Luclin\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use File;

class Module extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:make:module {path} {name} {space}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建模块';

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
        [
            'path'      => $path,
            'name'      => $name,
            'space'     => $space,
        ] = $this->arguments();

        if (file_exists("$path/composer.json")) {
            $this->info("target module is exists.");
            return;
        }

        // 复制目录
        // TODO: 暂未采取临时目录方案
        $source = \luc\mod('luclin')->path('resources', 'module-template');
        $cmd    = "cp -r $source/* $path/";
        exec($cmd);

        // 修改文件内容
        $vars = [
            'mymodule'      => $name,
            'MyModule'      => $space,
            'MyModule-ASED' => str_replace('\\', '\\\\', $space),
        ];
        foreach (File::allFiles($path) as $info) {
            $content    = file_get_contents($info->getRealPath());
            $newContent = \luc\padding($content, $vars);
            if ($content != $newContent) {
                file_put_contents($info->getRealPath(), $newContent);
            }
        }

    }

}
