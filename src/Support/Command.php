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
}
