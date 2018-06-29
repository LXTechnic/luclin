<?php

namespace Luclin;

use Illuminate\Contracts\Support as Contracts;

class Crash extends \Exception
    implements \JsonSerializable, Contracts\Arrayable, Contracts\Jsonable
{
    protected $extra;

    public function __construct(\Throwable $exc, array $extra = []) {
        parent::__construct($e->getMessage(), $e->getCode(), $e);

        $this->extra = $extra;
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

    public function toArray(): array {
        $exc    = $this->getPrevious();
        $result = [
            'message'   => $exc->message(),
            'code'      => $exc->getCode(),
            'file'      => $exc->getFile(),
            'line'      => $exc->getLine(),
            'extra'     => $this->extra,
        ];
        return $result;
    }

    public function jsonSerialize(): array {
        return $this->toArray();
    }

    public function toJson(): string {
        return json_encode($this);
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