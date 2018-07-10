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

    public static function createByUrl(string $url): self {
        $parsed = parse_url(urldecode($url));
        $query  = static::parseQuery($parsed['query'] ?? null);
        $luri   = new self($parsed['scheme'], $parsed['path'], $query);
        return $luri;
    }

    private static function parseQuery(?string $query): array {
        if (!$query) {
            return [];
        }
        parse_str(strtr($query, [
            '  '    => '&',
            '++'    => '&',
        ]), $result);
        return $result;
    }

    public function render($quote = false): string {
        $url = "$this->scheme:$this->path";
        $this->query && $url .= '?'.http_build_query($this->query);
        return $quote ? strtr($url, ['&' => '++']) : $url;
    }

    public function __toString(): string {
        return $this->render();
    }

    public function resolve(array $context = []) {
        $context = (new Context())->fill($context);

        // $offset = 0;
        // while ($pos = strpos($this->path, '/', $offset)) {
        //     yield substr($this->path, $offset, $pos - $offset)
        //         => [substr($this->path, $pos + 1), $this->query()];
        //     $offset = $pos + 1;
        // }
        // yield substr($this->path, $offset) => [null, $this->query()];

        $router = $this->scheme();
        $path   = explode('/', $this->path);
        while ($root = array_shift($path)) {
            $result = $router->$root($path, $this->query(), $context);

            if ($result instanceof Contracts\Router) {
                $router = $result;
            } elseif ($result instanceof Contracts\Endpoint) {
                return $result;
            } else {
                throw new \RuntimeException("Luri path node must be Router or Endpoint, ".get_class($result)." given.");
            }
        }
        return null;
    }

    public function scheme(): Luri\Scheme {
        return static::$registerSchemes[$this->scheme];
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
