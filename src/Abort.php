<?php

namespace Luclin;

use Illuminate\Contracts\Support as Contracts;

class Abort extends \Exception
    implements \JsonSerializable, Contracts\Arrayable, Contracts\Jsonable
{
    protected $extra;

    protected $level;

    protected $httpCode = 500;

    public function __construct(\Throwable $exc, array $extra = [],
        string $level = 'error')
    {
        parent::__construct($exc->getMessage(), $exc->getCode(), $exc);

        $this->extra = $extra;
        $this->level = $level;
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

    public function setHttpStatus($code): self {
        $this->httpCode = $code;
        return $this;
    }

    public function httpStatus(): array {
        return [$this->httpCode];
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