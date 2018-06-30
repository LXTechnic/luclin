<?php

namespace Luclin;

use Illuminate\Contracts\Support as Contracts;

class Abort extends \Exception
    implements \JsonSerializable, Contracts\Arrayable, Contracts\Jsonable
{
    const LEVEL_CODE_MAPPING = [
        'notice'    => 200,
        'warning'   => 403,
        'error'     => 400,
        'critical'  => 500,
    ];

    const LEVEL_NOTICE_MAPPING = [
        'notice'    => true,
        'warning'   => true,
        'error'     => false,
        'critical'  => false,
    ];

    protected $extra;

    protected $level;

    protected $httpCode;
    protected $httpHeaders = [];

    public $noticeOnly = false;

    public function __construct(\Throwable $exc, array $extra = [],
        string $level = 'warning')
    {
        parent::__construct($exc->getMessage(), $exc->getCode(), $exc);

        $this->extra = $extra;
        $this->level = $level;

        $this->httpCode     = self::LEVEL_CODE_MAPPING[$this->level];
        $this->noticeOnly   = self::LEVEL_NOTICE_MAPPING[$this->level];
    }

    public function __invoke() {
        return [
            $this->getPrevious(),
            $this->extra,
        ];
    }

	public function __toString(): string {
        return $this->toJson();
    }

    public function setHttpCode($code): self {
        $this->httpCode = $code;
        return $this;
    }

    public function setHttpHeaders(array $headers): self {
        $this->httpHeaders = $headers;
        return $this;
    }

    public function httpStatus(): array {
        return [$this->httpCode, $this->httpHeaders];
    }

    public function level(): string {
        return $this->level;
    }

    public function toArray(): array {
        $exc    = $this->getPrevious();
        $result = [
            'message'   => $exc->getMessage(),
            'code'      => $exc->getCode(),
            'file'      => $exc->getFile(),
            'line'      => $exc->getLine(),
            'level'     => $this->level,
            'extra'     => $this->extra,
        ];
        return $result;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    public function toJson($options = 0): string {
        return json_encode($this, $options);
    }

    public function class(): string {
        return get_class($this->getPrevious());
    }

    public function extra() {
        return $this->extra;
    }

    public function __debugInfo() {
        return $this->toArray();
    }
}