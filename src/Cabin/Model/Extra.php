<?php

namespace Luclin\Cabin\Model;

use Luclin\Cabin\Foundation\Model;

use Illuminate\Database\Schema\Blueprint;

abstract class Extra extends Model
{
    use Traits\Query;

    public function __construct(array $attributes = []) {
        $id = $attributes[$this->primaryKey] ?? $attributes["_$this->primaryKey"] ?? null;

        parent::__construct($attributes);
    }

    public static function makeWith($master): self {
        $model = new static();
        if (is_object($master)) {
            $model->setId($master->id());
        } elseif (is_array($master)) {
            $model->setId($master[$model->primaryKey] ?? $master["_$model->primaryKey"] ?? null);
        } else {
            $model->setId($master);
        }

        return $model;
    }

    protected static function migrateUpPrimary(Blueprint $table): void
    {
        $table->bigInteger('id');
        $table->primary('id');
    }

}