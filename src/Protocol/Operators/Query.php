<?php

namespace Luclin\Protocol\Operators;

use Luclin\Loader;
use Luclin\Contracts;
use Luclin\Uri;
use Luclin\Protocol\Operator;

class Query implements Contracts\Operator
{
    protected $uri;

    public function __construct(string $value) {
        $this->uri = app(Uri::class, [
            'uri'   => $value,
            'root'  => 'query',
        ]);
    }

    public function uri(): Uri {
        return $this->uri;
    }

    public function query(string $name, $value): self {
        $this->uri->setQuery($name, $value);
        return $this;
    }

    public function by(string $modelClass, bool $noMore = false): array {
        $querier = Loader::instance('querier')->make($this->uri->getPath(),
            $this->uri->getQuery(), $this->uri->fragment()->getSlice());

        // TODO: 这里的noMore在重构querier时要改掉
        $noMore && $querier->noMore();

        return [$querier->apply($modelClass::query()), $querier, $this->uri];
    }

}