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
 */
abstract class Slave extends Model
{
    use Traits\Query,
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
        $keys   = $this->getRouteKeyName();
        $where  = $this->defaultPrimaries();
        foreach (explode(',', $id) as $key => $value) {
            $where[$keys[$key]] = $value;
        }
        return static::found($where);
    }

    public function findId() {
        return ($this->master_type && $this->id)
            ? "$this->master_type,$this->id" : null;
    }

    abstract protected function defaultPrimaries(): array;

    abstract public function master(): ?object;
}