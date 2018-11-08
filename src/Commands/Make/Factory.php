<?php

namespace Luclin\Commands\Make;

use Luclin\Module;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use File;

class Factory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:make:factory {module} {model} {state?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为某个模块创建Factory';

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
            'module'    => $module,
            'model'     => $model,
            'state'     => $state,
        ] = $this->arguments();

        // 初步处理
        $model  = \luc\hyphen2class($model, '.');
        $name   = str_replace('\\', '', $model);

        // 细化处理
        if (!strpos($model, '\\')) {
            $model  = \luc\mod($module)->space()."\\Models\\$model";
        }
        $state  && ($name   .= 'State'.ucfirst($state));

        // 目录生成
        $path = \luc\mod($module)->path('database', 'factories');
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new \Exception("Factory directory [$path] is not exists.");
            }
        }

        // 生成文件
        $tmpName    = Str::random();
        $params     = [
            'name'      => $tmpName,
            '--model'   => $model,
        ];
        $this->call('make:factory', $params);

        // 内容更正
        $source = base_path('database'.DIRECTORY_SEPARATOR.
            'factories'.DIRECTORY_SEPARATOR.
            "$tmpName.php");
        $content = $this->updateName(file_get_contents($source),
            $tmpName, $name, $state);
        file_put_contents($source, $content);

        // 移动到模块中
        $dir    = $path.DIRECTORY_SEPARATOR;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $target = "$dir{$name}Factory.php";
        File::move($source, $target);
    }

    private function updateName(string $content, string $tmpName,
        string $name, ?string $state = null): string
    {
        $content = str_replace($tmpName, "{$name}Factory", $content);
        if ($state) {
            $content = str_replace('->define(', '->state(', $content);
            $content = str_replace('::class, ', "::class, '$state', ", $content);
        }
        return $content;
    }

}
