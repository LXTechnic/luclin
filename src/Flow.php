<?php

namespace Luclin;

use Luclin\Foundation\Domains\Blank;

/**
 */
class Flow implements Contracts\Tickable
{
    protected $_tags = [];

    protected $_through = [[null, null, [null, null]]];

    protected $_throughBackup = [];

    protected $_cursor  = 0;

    protected $_currentDomain = null;

    public static function new(...$tags): self {
        $flow = new static();
        $flow->_tags = $tags;
        return $flow;
    }

    public static function inherit(self $parent): self {
        $flow = new static();
        $flow->_currentDomain = $parent->_currentDomain;
        return $flow;
    }

    public function __call(string $action, array $arguments)
    {
        return $this->call($action, ...$arguments);
    }

    public static function __callStatic(string $action, array $arguments)
    {
        $flow = new static();
        return $flow->$action(...$arguments);
    }

    /**
     * run the flow
     *
     * @param string $defaultDomainClass
     * @param iterable $context
     * @return array
     */
    public function __invoke(string $defaultDomainClass = Blank::class,
        iterable $context = []): array
    {
        $this->_throughBackup = $this->_through;

        !($context instanceof Flow\Context)
            && $context = (new Flow\Context)->fill($context);

        $results = [];
        $next = $this;
        do {
            [$tick, $next] = $next->tick($defaultDomainClass, $context);

            if ($tick) {
                foreach ($tick() as $key => $result) {
                    $results[$key] = $result;
                }
            }
        } while ($next);
        return [$results, $context];
    }

    public function revert(): self {
        $this->_through = $this->_throughBackup;
        return $this;
    }

    public function tick(string $defaultDomainClass,
        Flow\Context $context): array
    {
        $current = array_shift($this->_through);
        if (!$current) {
            return [null, null];
        }
        if (count($current) < 2) {
            return [null, $current[0]];
        }
        [$next, $domainClass, [$action, $arguments]] = $current;
        if (!$action) {
            return [null, $next];
        }

        !$domainClass && $domainClass = $defaultDomainClass;
        $domain = $domainClass::instance();

        $tick   = $current ? function()
            use ($domain, $context, $action, $arguments)
        {
            return [$domain->id().'.'.$action => $domain->$action($context, ...$arguments)];
        } : $current;
        return [$tick, $next];
    }

    public function freeze(string $domainClass,
        array $context = []): Flow\Freeze
    {
        return new Flow\Freeze($this, $domainClass, $context);
    }

    public function at(?string $domainClass, callable $affluent = null): self {
        $this->_currentDomain = $domainClass;
        if ($affluent) {
            $affluent($this);
            $this->_currentDomain = null;
        }
        return $this;
    }

    public function call(string $action, ...$arguments): self {
        isset($this->_through[$this->_cursor])
            && $this->_through[$this->_cursor][0] = $this;
        $this->_cursor++;

        $this->_through[$this->_cursor] = [
            null,
            $this->_currentDomain,
            [$action, $arguments],
        ];
        return $this;
    }

    /**
     * 目前版本when不能用在flow的第一个
     *
     * @param mixed $inspector
     * @param callable|\Throwable $success
     * @param callable|\Throwable|null $fail
     * @return self
     */
    public function when($inspector, $success, $fail = null): self
    {
        $when = new Flow\When($this, $this->_currentDomain,
            $inspector, $success, $fail);

        isset($this->_through[$this->_cursor])
            && $this->_through[$this->_cursor][0] = $when;
        $this->_cursor++;

        $this->_through[$this->_cursor] = [
            null,
        ];
        return $this;
    }

    public function attempt(callable $way): Flow\Attempt {
        $attempt = new Flow\Attempt($this, $this->_currentDomain, $way);

        isset($this->_through[$this->_cursor])
            && $this->_through[$this->_cursor][0] = $attempt;
        $this->_cursor++;

        $this->_through[$this->_cursor] = [
            null,
        ];
        return $attempt;
    }

    public function try(callable $way): Flow\Attempt {
        return $this->attempt($way);
    }

    public function match(): self {

    }

    public function divert(?Dispatcher $dispatcher = null): self {
    }

    public function before() {

    }

    public function after() {

    }

    public function flow() {

    }
}
