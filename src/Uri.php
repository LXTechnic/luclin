<?php

namespace Luclin;

/**
 * 为精简模式下的uri进行适配
 */
class Uri
{
    const DEFAULT_RENDER = [
        'scheme',
        'root',
        'path',
        'query',
        'fragment',
    ];

    protected $defaultScheme = 'custom';

    private $scheme;

    private $root;
    private $path;
    private $query;
    private $fragment;

    public function __construct(string $uri, string $root = null)
    {
        $uri = $this->fixHttpAlias($uri);

        $parsed = parse_url($uri);
        $this->scheme   = $parsed['scheme'] ?? $this->defaultScheme;
        $this->path     = $parsed['path']   ?? '';
        $this->query    = $parsed['query']  ?? '';
        $this->fragment = $parsed['fragment']   ?? '';
        $root && $this->root = $root;

        $this->update();
    }

    private function fixHttpAlias(string $uri): string {
        $uri = strtr($uri, ["    " => '#']);
        $uri = strtr($uri, ["  " => '&']);
        return $uri;
    }

    public function render(...$parts): string {
        !$parts && $parts = self::DEFAULT_RENDER;

        $data = [];
        foreach ($parts as $part) {
            $data[$part] = $this->$part;
        }
        $data = $this->renderData($data);

        $uri = '';
        isset($data['scheme'])      && $uri  = $data['scheme'].':';
        isset($data['root'])        && $uri .= $data['root'].'/';
        isset($data['path'])        && $uri .= $data['path'];
        isset($data['query'])       && $uri .= '?'.http_build_query($data['query']);
        isset($data['fragment'])
            && $data['fragment']    && $uri .= '#'.$data['fragment'];
        return $uri;
    }

    public function update() {

        $this->path = trim($this->path, '/');
        if (!$this->root) {
            $pos = strpos($this->path, '/');
            if ($pos) {
                $this->root = substr($this->path, 0, $pos);
                $this->path = substr($this->path, ++$pos);
            } else {
                $this->root = $this->path;
                $this->path = '';
            }
        }

        if ($this->query) {
            parse_str($this->query, $result);
            $this->query = $result;
        }
        !$this->query && $this->query = [];

        $this->afterUpdate();
    }

    protected function renderData(array $data): array {
        return $data;
    }

    protected function afterUpdate(): void {}

    public function getRoot(): string {
        return $this->root;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function getQuery(string $name = null, $default = null) {
        return $name ? ($this->query[$name] ?? $default) : $this->query;
    }

    public function getFragment() {
        return $this->fragment;
    }

    public function setPath($path): self {
        $this->path = $path;
        return $this;
    }

    public function setQuery(string $name, $value): self {
        $this->query[$name] = $value;
        return $this;
    }

    public function setFragment(string $value): self {
        $this->fragment = $value;
        return $this;
    }

    public function fragment(): Contracts\Uri\FragmentPlug {
        $fragment = app(Contracts\Uri\FragmentPlug::class, [
            'value'     => $this->getFragment(),
            'parent'    => $this,
        ]);
        return $fragment;
    }

    public function __toString(): string {
        return $this->render();
    }
}
