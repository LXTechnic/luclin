<?php

namespace Luclin\Meta\Struct;

/**
 * 存取器支持
 *
 * @author andares
 */
trait AccessorsTrait {
    public function __set($key, $value) {
        $method = "_set_$key";
        if (method_exists($this, $method)) {
            $value = $this->$method($value);
        }
        $this->set($key, $value);
    }

    public function __get($key) {
        $value  = $this->get($key);

        $method = "_get_$key";
        if (method_exists($this, $method)) {
            $value = $this->$method($value);
        }
        return $value;
    }

    public function __isset($key) {
        $method = "_isset_$key";
        if (method_exists($this, $method)) {
            return $this->$method($this->has($key));
        }
        return $this->has($key);
    }

    public function __unset($key) {
        $method = "_unset_$key";
        if (method_exists($this, $method)) {
            if ($this->$method()) {
                return;
            }
        }
        $this->remove($key);
    }
}
