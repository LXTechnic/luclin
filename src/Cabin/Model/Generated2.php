<?php

namespace Luclin\Cabin\Model;

use Luclin\Cabin\Foundation\Model;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Builder;

abstract class Generated2 extends Model
{
    use Traits\Query;

    protected static $autoGenId = true;

    public $incrementing = false;
    protected $keyType = 'string';

    public function __construct(array $attributes = []) {
        if (static::$autoGenId) {
            $this->genId();
        }

        parent::__construct($attributes);
    }

    protected static function migrateUpPrimary(Blueprint $table): void
    {
        $table->string('id', 16);
        $table->primary('id');
    }

    public function genId(): string {
        $id = $this->genPrimaryId();
        return $this->setId($id)->id();
    }

    protected function genPrimaryId(): string {
        return \luc\gid();
    }

    public function save(array $options = []) {
        if (!$this->id()) {
            $this->genId();
        }
        return parent::save($options);
    }

}