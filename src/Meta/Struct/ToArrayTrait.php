<?php

namespace Luclin\Meta\Struct;

use Luclin\Support\Recursive;

use Illuminate\Contracts\Support\Arrayable;

/**
 *
 * @author andares
 */
trait ToArrayTrait {
    /**
     * 将对象展开为一个数组
     *
     * @param callable $filter
     * @return array
     */
    public function toArray(callable $filter = null): array {
        $toArray = new Recursive\ToArray($this->iterate(), $filter, static::_nullable());
        return $toArray();
    }

}
