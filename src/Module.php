<?php

namespace Luclin;


/**
 */
class Module
{
    protected $name;
    protected $space;
    protected $root;

    protected $mapping = [];

    public function __construct(string $name, string $space, string $root) {
        $this->name     = $name;
        $this->space    = $space;
        $this->root     = realpath($root);
    }

    public function name(): string {
        return $this->name;
    }

    public function space(): string {
        return $this->space;
    }

    public function setPathMapping(array $mapping, bool $merge = true): self {
        if ($merge) {
            $this->mapping = array_merge($this->mapping, $mapping);
        } else {
            $this->mapping = $mapping;
        }
        return $this;
    }

    public function path(string $category, ...$node) {
        $path = $this->mapping[$category] ??
            ($this->root.\DIRECTORY_SEPARATOR.$category);
        return $node
            ? ($path.\DIRECTORY_SEPARATOR.implode(\DIRECTORY_SEPARATOR, $node))
            : $path;
    }
}
