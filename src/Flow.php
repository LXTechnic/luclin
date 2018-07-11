<?php

namespace Luclin;

use Luclin\Flow\Role;

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

    public function __invoke($domain, ...$arguments) {
        if ($domain) {
            $domains = is_array($domain) ? $domain : [$domain];
        } else {
            $domains = [];
        }
        $domains[]  = Foundation\Domains\Common::class;

        if (isset($arguments[0]) && is_iterable($arguments[0])) {
            $context = array_shift($arguments);
            !($context instanceof Context)
                && ($context = (new Context())->fill($context));
        } else {
            $context = new Context();
        }
        $sandbox    = new Flow\Sandbox($domains, $context);
        $body       = $this->body;
        $result     = $body->call($sandbox, ...$arguments);
        return [($result instanceof Role) ? $result->raw() : $result, $context];
    }
}
