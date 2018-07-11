<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property string $type
 */
trait Typed
{
    protected static function migrateUpTyped(Blueprint $table,
        string $default = null): void
    {
        $f = $table->string('type', 50);
        $default ? $f->default($default) : $f->nullable();
    }

    protected static function migrateDownTyped(Blueprint $table): void
    {
        $table->dropColumn('type');
    }

    public function setTypeAttribute($value) {
        if (!in_array($value, static::getTypes())) {
            throw \luc\raise('luclin.model_type_attr_error');
        }
        $this->attributes['type'] = $value;
    }

    abstract protected static function getTypes(): array;
}