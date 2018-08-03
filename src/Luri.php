<?php

namespace Luclin;

class Luri
{
    private static $registerSchemes = [];

    private $scheme;
    private $path;
    private $query;

    public function __construct(string $scheme, string $path, array $query)
    {
        $this->scheme   = $scheme;
        $this->path     = $path;
        $this->query    = $query;
    }

    public static function registerScheme(string $name, Luri\Scheme $scheme): void {
        static::$registerSchemes[$name] = $scheme;
    }

    public static function createByUri(string $url): ?self {
        $parsed = parse_url($url);
        if (!isset($parsed['scheme'])) {
            return null;
        }
        $query  = static::parseQuery($parsed['query'] ?? null);
        $luri   = new self($parsed['scheme'], $parsed['path'], $query);
        return $luri;
    }

    private static function parseQuery(?string $query): array {
        if (!$query) {
            return [];
        }
        parse_str(static::unQuote($query), $result);
        return $result;
    }

    public static function quote(string $url): string {
        return strtr($url, ['&' => '++']);
    }

    public static function unQuote(string $url): string {
        return strtr($url, [
            '  '    => '&',
            '++'    => '&',
        ]);
    }

    public function render(array $query = null, $quote = false): string {
        $url    = "$this->scheme:$this->path";
        $query  = $query ? array_merge($this->query, $query) : $this->query;
        $query  && ksort($query);
        $query  && $url .= '?'.http_build_query($query);
        return $quote ? static::quote($url) : $url;
    }

    public function __toString(): string {
        return $this->render();
    }

    public function toArray(): array {
        return [
            $this->scheme,
            $this->root(),
            $this->path(false),
            $this->query(),
        ];
    }

    public function toOperator($onlyValue = true): string {
        $value = $this->path(false).
            ($this->query ? ('?'.http_build_query($this->query)) : '');
        return $onlyValue ? $value : ('$'.$this-root()."=$value");
    }

    public function resolve(array $context = []): array {
        $context = (new Context())->fill($context);
        $context->_luri = $this;

        // $offset = 0;
        // while ($pos = strpos($this->path, '/', $offset)) {
        //     yield substr($this->path, $offset, $pos - $offset)
        //         => [substr($this->path, $pos + 1), $this->query()];
        //     $offset = $pos + 1;
        // }
        // yield substr($this->path, $offset) => [null, $this->query()];

        [$_, $router] = $this->scheme();
        $path   = explode('/', $this->path);
        while ($root = array_shift($path)) {
            $result = $router->$root($path, $this->query(), $context);
            if (!$result) {
                throw new \RuntimeException("Luri route [$this->path] fail.");
            }

            if ($result instanceof Contracts\Router) {
                $router = $result;
            } elseif ($result instanceof Contracts\Endpoint) {
                return [$result, $root];
            } else {
                throw new \RuntimeException("Luri path node must be Router or Endpoint, ".get_class($result)." given.");
            }
        }
        return [null, null];
    }

    public function scheme(): array {
        return [$this->scheme, static::$registerSchemes[$this->scheme]];
    }

    public function root(): string {
        $pos = strpos($this->path, '/');
        return $pos ? substr($this->path, 0, $pos) : $this->path;
    }

    public function path(bool $includeRoot = true): string {
        if ($includeRoot) {
            return $this->path;
        }
        $pos = strpos($this->path, '/');
        return $pos ? substr($this->path, $pos + 1) : '';
    }

    public function query(): array {
        return $this->query;
    }

    public function __get(string $name) {
        return $this->query[$name] ?? null;
    }

    public function __set(string $name, $value): void {
        $this->query[$name] = $value;
    }
}
