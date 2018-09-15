<?php

namespace Luclin\Cabin\Model;

use Luclin\Cabin\Foundation\Model;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Builder;

/**
 * 该类型使用find系列方法读取时表现和Master类型没有区别。
 * 只有在使用found时才能支持其特点。
 */
abstract class Enum extends Model
{
    use Traits\Query,
        Traits\MoreKeys;

    protected static function migrateUpPrimary(Blueprint $table, ...$keys): void
    {
        $table->increments('id');
        $table->unique($keys);
    }

    public function resolveRouteBinding($id)
    {
        return static::found($id);
    }

    // TODO: 因为带自增主键，所以理论上应该不需要设置findId
    // public function findId() {
    // }

    abstract protected static function defaultKeys(): array;
}