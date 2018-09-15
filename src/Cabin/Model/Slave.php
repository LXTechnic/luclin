<?php

namespace Luclin\Cabin\Model;

use Luclin\Cabin\Foundation\{
    Model,
    HasCompositePrimaryKeyTrait
};

use Illuminate\Database\Schema\Blueprint;
use DB;

/**
 *
 * 关于该类型的单记录查取方法：
 * find() 方法参数只能是 '<master_type>,<id>'这种
 * findMany() 中每一项同上
 * found() 方法才允许在仅填写id的时候进行查询
 */
abstract class Slave extends Model
{
    use Traits\Query,
        Traits\MoreKeys,
        HasCompositePrimaryKeyTrait;

    protected $primaryKey = ['id', 'master_type'];
    public $incrementing = false;
    protected $keyType = 'string';

    protected static function migrateUpPrimary(Blueprint $table): void
    {
        $table->string('master_type', 50);
        $table->string('id', 50);
        $table->primary(['id', 'master_type']);
    }

    public static function findMany(array $ids)
    {
        [$masterType] = explode(',', $ids[0]);
        foreach ($ids as $key => $id) {
            [$_, $id] = explode(',', $id);
            $ids[$key] = $id;
        }

        return static::query()
            ->where('master_type', $masterType)
            ->whereIn('id', $ids)
            ->get();
    }

    /**
     * 这里是注入实现的路由绑定
     *
     * @param mixed $id
     * @return self
     */
    public function resolveRouteBinding($id)
    {
        return static::found($id);
    }

    public function findId() {
        return ($this->master_type && $this->id)
            ? "$this->master_type,$this->id" : null;
    }

    abstract public function master(): ?object;
}