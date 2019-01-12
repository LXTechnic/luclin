<?php

namespace Luclin\Support;

use Illuminate\Console\Application as Artisan;
use File;

class Command
{
    public static function register(string $prefix, string $directory): void {
        $directory  = realpath($directory);
        if (!$directory) {
            return;
        }
        $rootLength = strlen($directory);
        $commands   = [];
        foreach (File::allFiles($directory) as $info) {
            if (strtolower($info->getExtension()) != 'php') {
                continue;
            }
            if (strpos($info->getBasename(), 'Trait.')) {
                continue;
            }
            $filename   = $info->getPathname();
            $commands[] = "$prefix".
                str_replace(DIRECTORY_SEPARATOR, '\\', substr($filename, $rootLength, -4));
        }
        Artisan::starting(function ($artisan) use ($commands) {
            $artisan->resolveCommands($commands);
        });
    }

    public static function active(...$classes): void {
        // 原来以为要取到文件，发现只要class就可以了
        // $module = \luc\mod($module);
        // $path   = strtr(substr($class, strlen($module->space()) + 1), '\\', '/');
        // $file   = $module->path('src', "$path.php");
        Artisan::starting(function ($artisan) use ($classes) {
            $artisan->resolveCommands($classes);
        });
    }
}
