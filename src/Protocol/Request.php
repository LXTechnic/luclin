<?php

namespace Luclin\Protocol;

use Luclin\Meta\Struct;
use Luclin\Uri;
use Luclin\MetaInterface;

use Validator;
use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Foundation\Http\FormRequest;
use Luclin\Foundation\Http\Middleware\XHeaders;

class Request extends Struct
    implements \Luclin\MetaInterface
{
    use Foundation\OperableTrait;

    protected $raw;

    public function __construct(LaravelRequest $raw) {
        $this->raw = $raw;

        $this->fill($raw->toArray());
    }

    protected static function _validate(): array {
        return [];
    }

    protected static function _hints(): array {
        return [[], []];
    }

    public function xheader(): XHeaders {
        return \luc\ins('xheaders');
    }

    public function raw(): LaravelRequest {
        return $this->raw;
    }

    public function __call(string $name, array $arguments) {
        return $this->raw->$name(...$arguments);
    }

    public function confirm(): MetaInterface {
        $validator = Validator::make($this->toArray(), static::_validate(),
            ...static::_hints());
        if ($validator->fails()) {
            throw new \InvalidArgumentException($validator->errors()->first());
        }
        return parent::confirm();
    }
}
