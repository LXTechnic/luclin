<?php

namespace Luclin\Meta;

use Luclin\Contracts;

use Illuminate\Support;
use Illuminate\Contracts\Support as ContractsSupport;

/**
 * 标准Collection基类
 *
 * @author andares
 */
class Collection extends Support\Collection
    implements Contracts\Meta,
    \ArrayAccess, \Countable, \JsonSerializable, \IteratorAggregate,
    ContractsSupport\Arrayable, ContractsSupport\Jsonable
{
    use CollectionTrait;
}
