<?php

namespace Luclin;

/**
 */
class Flow
{
    use Foundation\SingletonNamedTrait;

    private $body;

    public function __construct(callable $body) {
        $this->body = $body;
    }

    public function save(string $name): self {
        return self::instance($name, $this->body);
    }

    public function __invoke($domain, iterable $context, ...$arguments) {
        if ($domain) {
            $domains = is_array($domain) ? $domain : [$domain];
        } else {
            $domains = [];
        }

        $domains[]  = Foundation\Domains\Common::class;
        !($context instanceof Flow\Context)
            && ($context = (new Flow\Context)->fill($context));
        $sandbox    = new Flow\Sandbox($domains, $context);
        $body = $this->body;
        return [$body->call($sandbox, ...$arguments), $context];
    }
}
