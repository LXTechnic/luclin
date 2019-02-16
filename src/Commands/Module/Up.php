<?php

namespace Luclin\Commands\Module;

use Luclin\Support\Composer\Fork\JsonFormatter;
use Luclin\Support\Recursive\FileFinder;
use Symfony\Component\Yaml\Yaml;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use File;

/**
 * update for modules
 */
class Up extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'luc:module:up {directories?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '指定模块目录更新并安装模块';

    private $phpUnitXml = null;

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
        $baseComposerConf = file_get_contents(base_path('composer.json'));
        $baseComposerConf = json_decode($baseComposerConf, true);

        $directories = $this->argument('directories') ?: [base_path('modules')];
        $requires    = [];
        foreach ($directories as $dir) {
            $it = new FileFinder($dir, function ($fileinfo) {
                return $fileinfo->getBasename() == 'composer.json';
            });
            foreach ($it() as $file) {
                $modulePath     = $file->getPath();
                $content        = file_get_contents($file->getRealPath());
                $composerConf   = json_decode($content, true);
                $baseComposerConf['repositories'][$composerConf['name']] = [
                    'type'  => 'path',
                    'url'   => $modulePath,
                ];
                $baseComposerConf['require'][$composerConf['name']] = '*';
                $requires[] = $composerConf['name'];

                // 单元测试目录加入
                $this->prepareTest($modulePath);
            }
        }

        // 单元测试配置写入
        file_put_contents(base_path('phpunit.xml'), $this->phpUnitXml);

        $newComposerConf = JsonFormatter::format(json_encode($baseComposerConf));
        file_put_contents(base_path('composer.json'), $newComposerConf);

        exec("composer update ".implode(' ', $requires), $output);
        if ($output) foreach ($output as $line) {
            $this->info($line);
        }

        // 自动放出luc
        if (!File::exists('luc')) {
            exec("cp ./vendor/bin/luc ./");
        }
    }

    private function prepareTest(string $modulePath): void {
        $phpUnitXml = $this->getPhpUnitXml();

        $relationRoot   = substr($modulePath, strlen(base_path()));
        $testsDirectory = ".$relationRoot".DIRECTORY_SEPARATOR.'tests';

        $names = [
            'Feature',
            'Unit',
        ];
        foreach ($names as $name) {
            $path = $testsDirectory.DIRECTORY_SEPARATOR.$name;
            if (!strpos($phpUnitXml, $path)) {
                $phpUnitXml = $this->appendTestSuite($phpUnitXml, $name, $path);
            }
        }
        $this->phpUnitXml = $phpUnitXml;
    }

    private function getPhpUnitXml(): string {
        if ($this->phpUnitXml === null) {
            $this->phpUnitXml = file_get_contents(base_path('phpunit.xml'));
        }
        return $this->phpUnitXml;
    }

    private function appendTestSuite(string $phpUnitXml,
        string $name, string $path): string
    {
        $pattern = <<<EOT


        <testsuite name="$name">
            <directory suffix="Test.php">$path</directory>
        </testsuite>
EOT;
        return substr_replace($phpUnitXml, $pattern,
            strpos($phpUnitXml, '    </testsuites>') - 1, 0);
    }

}
