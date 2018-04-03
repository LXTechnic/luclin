<?php

namespace Luclin\Context;

use Luclin\Contracts;

/**
 * @see 在inject方法中使用mapping字段，请使用在闭包声明```$role = $this; .. use ($role)```来访问映射字段
 */
abstract class Role implements Contracts\Data
{
    public $alias = null;

    protected static $_mapping = [];

    /**
     * @var Contracts\Data
     */
    protected $_data;

    public function raw() {
        return $this->_data instanceof self ? $this->_data->raw() : $this->_data;
    }

    public function __construct(Contracts\Data $data) {
        if (!$this->verify($data)) {
            throw new \InvalidArgumentException('The role: '.static::class.' is not match the data.');
        }
        $this->_data = $data;
    }

    public function __call(string $name, array $arguments) {
        $method = "inject".ucfirst($name);
        if (method_exists($this, $method)) {
            $interaction = $this->$method();
            return $interaction->call($this->_data, ...$arguments);
        }
        return $this->_data->$name(...$arguments);
    }

    public function __get(string $name) {
        $field = isset(static::$_mapping[$name]) ? static::$_mapping[$name] : $name;
        return $this->_data->$field;
    }

    public function __set(string $name, $value) {
        $field = isset(static::$_mapping[$name]) ? static::$_mapping[$name] : $name;
        return $this->_data->$field = $value;;
    }

    protected function verify(Contracts\Data $data): bool {
        return true;
    }

}