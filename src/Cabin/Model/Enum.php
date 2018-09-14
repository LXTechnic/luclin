<?php

namespace Luclin\Cabin\Model;

use Luclin\Cabin\Foundation\Model;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Builder;

abstract class Enum extends Model
{
    use Traits\Query;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function migrateUpPrimary(Blueprint $table): void
    {
        $table->string('id', 50);
        $table->primary('id');
    }

    public function resolveRouteBinding($id)
    {
        $model  = static::found(['id' => $id]);
        return $model;
    }

}