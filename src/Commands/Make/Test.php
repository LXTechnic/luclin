<?php

namespace Luclin\Commands\Make;

use Luclin\Module;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use File;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:make:test {module} {name} {--unit}';

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
        // 获取参数
        [
            'module'    => $module,
            'name'      => $name,
        ] = $this->arguments();
        @[
            'unit'      => $unit,
        ] = $this->options();
        [$name, $category] = $this->getNameAndCategory($name);

        // 目录生成
        $path = $unit ? \luc\mod($module)->path('tests', 'Unit')
            : \luc\mod($module)->path('tests', 'Feature');
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new \Exception("Test directory [$path] is not exists.");
            }
        }

        // 目标文件路径生成
        $dir    = $category
            ? (str_replace('\\', DIRECTORY_SEPARATOR, $category).DIRECTORY_SEPARATOR)
                : '';
        $dir    = $path.DIRECTORY_SEPARATOR.$dir;
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        $target = "$dir{$name}Test.php";
        if (File::exists($target)) {
            $this->info("<skipped> Target file [".basename($target)."] is exists.");
            return;
        }

        // 生成文件
        $tmpName    = Str::random();
        $params     = [
            'name'  => $tmpName,
        ];
        $unit   && $params['--unit']    = $unit;
        $this->call('make:test', $params);

        // 内容更正
        $source = base_path('tests'.DIRECTORY_SEPARATOR.
            ($unit ? 'Unit' : 'Feature').DIRECTORY_SEPARATOR.
            "$tmpName.php");
        $content = $this->updateName(\luc\mod($module), file_get_contents($source),
            $tmpName, $name, $category, $unit);
        file_put_contents($source, $content);

        // 移动到模块中
        File::move($source, $target);
    }

    private function getNameAndCategory(string $name): array {
        $name       = \luc\hyphen2class($name);
        $category   = null;
        if ($pos = strrpos($name, '\\')) {
            $category   = substr($name, 0, $pos);
            $name       = substr($name, $pos + 1);
        }
        return [$name, $category];
    }

    private function updateName(Module $module, string $content,
        string $tmpName, string $name,
        ?string $category, bool $isUnit): string
    {
        $space   = $module->space();
        $suffix  = $isUnit ? 'Tests\\Unit' : 'Tests\\Feature';
        $replace = "namespace $space\\$suffix".($category ? "\\$category;" : ';');
        $content = str_replace("namespace $suffix;", $replace, $content);

        $content = str_replace($tmpName, "{$name}Test", $content);
        return $content;
    }

}
