<?php

namespace Luclin\Support\Recursive;

class FileFinder
{
    private $root;
    private $check;

    public $skipHidden = true;

    public function __construct(string $root, callable $check = null)
    {
        $this->root     = $root;
        $this->check    = $check;
    }

    public function __invoke(): iterable {
        foreach ($this->searchDir($this->root) as $fileinfo) {
            $check = $this->check;
            if ($check && !$check($fileinfo)) {
                continue;
            }
            yield $fileinfo;
        }
    }

    private function searchDir(string $dir) {
        $it = new \DirectoryIterator($dir);
        foreach ($it as $fileinfo) {
            if ($fileinfo->isDot()) {
                continue;
            }
            if ($fileinfo->isLink() || $fileinfo->isDir()) {
                if ($this->skipHidden && $fileinfo->getBasename()[0] == '.') {
                    continue;
                }

                foreach ($this->searchDir($fileinfo->getRealPath()) as $file) {
                    yield $file;
                }
            } else {
                yield $fileinfo;
            }
        }
    }
}