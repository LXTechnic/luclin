<?php

namespace Luclin\Meta;

use Illuminate\Support;
use Illuminate\Contracts\Support as Contracts;

/**
 * 标准Collection基类
 *
 * @author andares
 */
class Collection extends Support\Collection
    implements \Luclin\MetaInterface,
    \ArrayAccess, \Countable, \JsonSerializable, \IteratorAggregate,
    Contracts\Arrayable, Contracts\Jsonable
{
    use CollectionTrait;
}
