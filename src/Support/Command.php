<?php

namespace Luclin\Support;

use Illuminate\Console\Application as Artisan;
use File;

class Command
{
    public static function register(string $prefix, ...$directories): void {
        $commands = [];
        foreach ($directories as $directory) {
            foreach (File::allFiles() as $info) {
                if (strtolower($info->getExtension()) != 'php') {
                    continue;
                }
                $commands[] = "$prefix\\".$info->getBasename('.php');
            }
        }
        Artisan::starting(function ($artisan) use ($commands) {
            $artisan->resolveCommands($commands);
        });
    }
}
