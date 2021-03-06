<?php

namespace Luclin\Luri;

use Luclin\Contracts;
use Luclin\Luri;

use Validator;

/**
 * 预设机制。
 *
 * 在config中，支持patterns和defaults。
 * 在通过uri初始化时，context支持config，defaults以及vars。
 */
class Preset implements Contracts\Endpoint, Contracts\Operator
{
    public $validate = [];
    public $hints    = [];

    public $parsed;

    protected $name;
    protected $vars = [];
    protected $patterns;
    protected $defaults;

    protected $luri;

    public function __construct(string $name, array $config = []) {
        $this->name     = $name;
        if (!isset($config[$name])) {
            throw new \RuntimeException("Preset config [$name] is not found");
        }
        $this->patterns = $config[$name]['patterns'] ?? [];
        $this->defaults = $config[$name]['defaults'] ?? [];

        $this->validate = $config[$name]['validate'] ?? [];
        $this->hints    = $config[$name]['hints']    ?? [];
    }

    /**
     * 在通过luri构建preset时，仅vars和defaults支持在context里补充，其他的要走config
     *
     * @param array $arguments
     * @param array $options
     * @param Contracts\Context $context
     * @return Contracts\Endpoint
     */
    public static function new(array $arguments, array $options,
        Contracts\Context $context): Contracts\Endpoint
    {
        $preset = new static($arguments[0], $context->config ?: []);
        $preset->assign($options);

        $context->defaults  && $preset->applyDefaults($context->defaults);
        $context->vars      && $preset->applyVars($context->vars);

        $preset->luri = $context->_luri;
        return $preset;
    }

    public function name(): string {
        return $this->name;
    }

    public function applyDefaults(array $defaults): self {
        $this->vars = array_merge($defaults, $this->vars);
        return $this;
    }

    public function applyVars(array $vars): self {
        $this->vars = array_merge($this->vars, $vars);
        return $this;
    }

    public function parse(): array {
        $this->validate();

        $this->parsed = [];

        $pattern = $this->patterns;
        if ($pattern) foreach ($pattern as $key => $row) {
            $row = \luc\padding($row, $this->vars);
            $endpoint = \luc\uri($row);
            if ($endpoint) {
                $this->parsed[$key] = $endpoint;
            } else {
                parse_str(Luri::unQuote($row), $params);
                $this->parsed[$key] = $params;
            }
        }
        return $this->parsed;
    }

    public function render(array $query = null, $quote = false): string {
        $query  = $query ? array_merge($this->vars, $query) : $this->vars;
        $url    = "$this->name?".http_build_query($query);
        return $quote ? Luri::quote($url) : $url;
    }

    public function assign(array $vars): self {
        $this->vars = array_merge($this->defaults, $vars);
        return $this;
    }

    public function setPattern(string $name, array $pattern): self {
        $this->patterns[$name] = $pattern;
        return $this;
    }

    public function __get(string $name) {
        return $this->vars[$name] ?? null;
    }

    public function __set(string $name, $value): void {
        $this->vars[$name] = $value;
    }

    protected function validate(): void {
        if (!$this->validate) {
            return;
        }

        $validator = Validator::make($this->vars, $this->validate, ...$this->hints);
        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
    }
}