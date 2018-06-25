<?php

namespace Luclin\Support;

/**
 */
class Baseline
{
    private $repos  = [];
    private $snap   = [];
    private $trail  = [];

    public function __construct(array $topConf, array $caseConf = null) {
        $this->repos    = $topConf['repos'] ?? [];
        $this->snap     = $topConf['snap']  ?? [];
        $this->trail    = $topConf['trail'] ?? [];

        if ($caseConf) {
            $this->repos = array_merge($this->repos, $caseConf['repos'] ?? []);
            $this->snap  = array_merge($this->snap,  $caseConf['snap']  ?? []);
            $caseConf['trail'] && $this->trail = $caseConf['trail'];
        }
    }

    public function applySnap(string $name) {
        if (!isset($this->snap[$name])) {
            throw new \Exception("Snap [$name] is not found.");
        }
        foreach ($this->snap[$name]['line'] as $repo => $tag) {
            if (!isset($this->repos[$repo])) {
                throw new \Exception("Repository [$repo] is not found.");
            }
            yield [$this->repos[$repo]['dir'], $this->repos[$repo]['url'], $tag];
        }
    }

    public function checkout(string $dir, string $url, string $tag): void {
        $baseDir = dirname($dir);
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
        if (!file_exists($dir)) {
            $cmd = "git clone $url $dir";
            exec($cmd);
        } else {
            $cmd = "git -C $dir pull";
            exec($cmd);
        }
        $cmd = "git -C $dir checkout $tag";
        exec($cmd);
    }

    public function renew(string $dir, string $url, string $branch) {
        $baseDir = dirname($dir);
        if (!file_exists($baseDir)) {
            mkdir($baseDir, 0755, true);
        }
        if (!file_exists($dir)) {
            $cmd = "git clone $url $dir";
            exec($cmd);
            $cmd = "git -C $dir checkout $branch";
            exec($cmd);
        }
        $cmd = "git -C $dir fetch --all";
        exec($cmd);
        $cmd = "git -C $dir reset --hard origin/$branch";
        exec($cmd);
    }
}
