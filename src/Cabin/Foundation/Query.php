<?php

namespace Luclin\Cabin\Foundation;

use Luclin\Contracts;
use Luclin\Loader;
use Luclin\Luri;

class Query implements Contracts\Router
{


    // public function query(string $name, $value): self {
    //     $this->uri->setQuery($name, $value);
    //     return $this;
    // }

    // public function by(string $modelClass, bool $noMore = false): array {
    //     $querier = Loader::instance('querier')->make($this->uri->getPath(),
    //         $this->uri->getQuery(), $this->uri->fragment()->getSlice());

    //     // TODO: 这里的noMore在重构querier时要改掉
    //     $noMore && $querier->noMore();

    //     return [$querier->apply($modelClass::query()), $querier, $this->uri];
    // }

}