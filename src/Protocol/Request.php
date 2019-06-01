<?php

namespace Luclin\Protocol;

use Luclin\Contracts;
use fun\variant;
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

    protected static function _mapping(): array {
        return [];
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
        foreach ($this->toArray() as $key => $value) {
            if ($value !== null) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public function toArray(callable $filter = null, bool $applyMapping = true,
        bool $fillable = true): array
    {
        $arr = parent::toArray($filter);

        if ($fillable) {
            $raw = $this->raw->toArray();
            foreach ($arr as $key => $value) {
                if ($value instanceof variant) {
                    if (array_key_exists($key, $raw) &&
                        ($raw[$key] === null || $value()->search($raw[$key]) !== false))
                    {
                        $arr[$key] = null;
                    } else {
                        unset($arr[$key]);
                    }
                }
            }
        }

        if ($applyMapping) foreach (static::_mapping() as $from => $to) {
            if (array_key_exists($from, $arr)) {
                $arr[$to] = $arr[$from];
                unset($arr[$from]);
            }
        }
        return $arr;
    }

    public function confirm(): Contracts\Meta {
        $validator = Validator::make($this->toArray(null, false), static::_validate(),
            ...static::_hints());
        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
        return parent::confirm();
    }
}
