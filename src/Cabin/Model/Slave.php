<?php

namespace Luclin\Cabin\Model;

use Luclin\Cabin\Foundation\{
    Model,
    HasCompositePrimaryKeyTrait
};

use Illuminate\Database\Schema\Blueprint;
/**
 *
 * 请在use类中加下以下属性：
 *
 * protected $primaryKey = ['id', 'master_type'];
 * public $incrementing = false;
 */
class Slave extends Model
{
    use Traits\Query,
        HasCompositePrimaryKeyTrait;

    protected static function migrateUpPrimary(Blueprint $table): void
    {
        $table->string('master_type', 50);
        $table->string('id', 50);
        $table->primary(['id', 'master_type']);
    }

    public static function find($id) {
        $model = new static();
        $where = [];
        foreach (explode(',', $id) as $key => $value) {
            $where[$model->primaryKey[$key]] = $value;
        }
        return static::found($where);
    }
}