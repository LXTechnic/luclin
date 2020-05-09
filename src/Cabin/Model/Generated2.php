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

        // 自动执行 setUp
        foreach ((new \ReflectionClass(static::class))->getMethods() as $method) {
            if (!$method->isPublic() &&
                !$method->isStatic() &&
                strlen($method->getName()) > 5 &&
                substr($method->getName(), 0, 5) == 'setUp')
            {
                $methodName = $method->getName();
                $this->$methodName();
            }
        }
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