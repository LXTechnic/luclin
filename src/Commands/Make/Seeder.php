<?php

namespace Luclin\Commands\Make;

use Luclin\Module;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use File;

class Seeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:make:seeder {module} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为某个模块创建Seeder';

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
        $name = ucfirst($name);

        // 目录生成
        $path = \luc\mod($module)->path('database', 'seeds');
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new \Exception("Seed directory [$path] is not exists.");
            }
        }

        // 生成文件
        $tmpName    = Str::random();
        $params     = [
            'name'  => $tmpName,
        ];
        $this->call('make:seed', $params);

        // 内容更正
        $source = base_path('database'.DIRECTORY_SEPARATOR.
            'seeds'.DIRECTORY_SEPARATOR.
            "$tmpName.php");
        $content = $this->updateName(file_get_contents($source),
            $tmpName, $name);
        file_put_contents($source, $content);

        // 移动到模块中
        $dir    = $path.DIRECTORY_SEPARATOR;
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $target = "$dir{$name}Seeder.php";
        File::move($source, $target);

        exec('composer dump-autoload');
    }

    private function updateName(string $content,
        string $tmpName, string $name): string
    {
        $content = str_replace("<?php\n", "<?php\n\nnamespace Seeds;\n", $content);

        $content = str_replace($tmpName, "{$name}Seeder", $content);
        return $content;
    }

}
