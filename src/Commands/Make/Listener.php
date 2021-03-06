<?php

namespace Luclin\Commands\Make;

use Luclin\Module;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use File;

use Illuminate\Console\DetectsApplicationNamespace;

class Listener extends Command
{
    use DetectsApplicationNamespace;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:make:listener {module} {name} {--e|event=} {--queued}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为某个模块创建Listener';

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
        $name       = ucfirst($name);

        [
            'queued'    => $queued,
            'event'     => $event,
        ] = $this->options();

        // 目录生成
        $path = \luc\mod($module)->path('src', 'Listeners');
        if (!file_exists($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new \Exception("Seed directory [$path] is not exists.");
            }
        }

        // 生成文件
        $tmpName    = 'Tmp'.Str::random();
        $params     = [
            'name'  => $tmpName,
        ];
        $queued && $params['--queued']  = true;
        $event  && $params['--event']   = $event;
        $this->call('make:listener', $params);

        // 内容更正
        $source = base_path('app'.DIRECTORY_SEPARATOR.
            'Listeners'.DIRECTORY_SEPARATOR.
            "$tmpName.php");
        $content = $this->update(file_get_contents($source), $tmpName, $name,
            \luc\mod($module)->space());
        file_put_contents($source, $content);

        // 移动到模块中
        $target = $path.DIRECTORY_SEPARATOR."$name.php";
        $dir    = dirname($target);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        File::move($source, $target);
    }

    private function update(string $content, string $tmpName,
        string $name, string $namespace): string
    {
        $name   = str_replace('/', '\\', $name);
        $pos    = strrpos($name, '\\');
        if ($pos) {
            $className  = substr($name, $pos + 1);
            $prefix     = '\\'.substr($name, 0, $pos);
        } else {
            $className  = $name;
            $prefix     = '';
        }
        $fromNamespace  = $this->getAppNamespace()."Listeners";
        $toNamespace    = $namespace."\\Listeners$prefix";
        $content = str_replace("namespace $fromNamespace",
            "namespace $toNamespace", $content);

        $content = str_replace("class $tmpName", "class $className", $content);

        return $content;
    }

}
