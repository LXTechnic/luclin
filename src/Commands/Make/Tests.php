<?php

namespace Luclin\Commands\Make;

use Luclin\Module;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use File;

class Tests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:make:tests {module}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为某个模块自动创建所有未创建的测试用例';

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
        ] = $this->arguments();

        $conf = file_get_contents(\luc\mod($module)->path('composer.json'));
        $conf = json_decode($conf, true)['extra']['test'] ?? [];
        if (isset($conf['feature'])) foreach ($conf['feature'] as $path) {
            foreach (File::allFiles(\luc\mod($module)->path($path)) as $info) {
                $this->makeTest($module, $info, false);
            }
        }
        if (isset($conf['unit'])) foreach ($conf['unit'] as $path) {
            foreach (File::allFiles(\luc\mod($module)->path($path)) as $info) {
                $this->makeTest($module, $info, true);
            }
        }
    }

    private function makeTest(string $module, \SplFileInfo $info,
        bool $isUnit = false): void
    {
        if ($info->getExtension() != 'php') {
            return;
        }
        $content = file_get_contents($info->getRealPath());
        $class   = $info->getBasename('.php');
        if (!strpos($content, "\nclass $class")) {
            return;
        }
        if (!preg_match('/namespace ([a-zA-Z\\\\]+);/', $content, $matches)) {
            return;
        }
        $path   = str_replace(\luc\mod($module)->space(), '', $matches[1]);
        if ($path) {
            $path   = substr($path, 1);
            $name   = strtr($path, '\\', '/')."/$class";
        } else {
            $name   = $class;
        }

        $params     = [
            'module'    => $module,
            'name'      => $name,
        ];
        $isUnit && $params['--unit']    = $isUnit;
        $this->call('luc:make:test', $params);
    }

}
