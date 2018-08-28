<?php

namespace Luclin\Commands\Baseline;

use Symfony\Component\Yaml\Yaml;

trait CommonTrait
{
    protected function conf(): array {
        $path = base_path('.baseline.yml');
        try {
            if (file_exists($path)) {
                $conf = Yaml::parse(file_get_contents($path));
            } else {
                $conf = [];
            }
        } catch (\Throwable $exc) {
            throw $exc;
        }

        $name = $this->option('conf');
        if ($name != '?') {
            $path = base_path('.baseline'.DIRECTORY_SEPARATOR."$name.yml");
            try {
                if (!file_exists($path)) {
                    throw new \RuntimeException("Baseline config [$name] not found.");
                }
                $conf = array_merge($conf, Yaml::parse(file_get_contents($path)));
            } catch (\Throwable $exc) {
                throw $exc;
            }
        }
        return $conf;
    }
}