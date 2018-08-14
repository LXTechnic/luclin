<?php

namespace Luclin\Protocol;

use Luclin\Contracts;
use Luclin\Meta\Struct;

use Validator;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Foundation\Http\FormRequest;
use Luclin\Foundation\Http\Middleware\XHeaders;

class Request extends Struct
    implements Contracts\Meta
{
    use Foundation\OperableTrait;

    protected $raw = null;

    public function __construct(LaravelRequest $raw = null) {
        if ($raw) {
            $this->raw = $raw;
            $this->fill($raw->toArray());
        }
    }

    protected static function _nullable(): ?array {
        return null;
    }

    protected static function _validate(): array {
        return [];
    }

    protected static function _hints(): array {
        return [[], []];
    }

    public function raw(): LaravelRequest {
        return $this->raw;
    }

    public function __call($name, $arguments) {
        if (static::hasMacro($name)) {
            return parent::__call($name, $arguments);
        }
        return $this->raw->$name(...$arguments);
    }

    public function toArrayWithoutNull(): array {
        $result = [];
        foreach (parent::toArray() as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function confirm(): Contracts\Meta {
        $validator = Validator::make($this->toArray(), static::_validate(),
            ...static::_hints());
        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
        return parent::confirm();
    }
}
