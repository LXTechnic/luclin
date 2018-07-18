<?php

namespace Luclin\Cabin\Model;

use Luclin\Cabin\Foundation\Model;

use Illuminate\Database\Schema\Blueprint;

class Master extends Model
{
    use Traits\Query;

    protected static function migrateUpPrimary(Blueprint $table,
        bool $isBig = false): void
    {
        $isBig ? $table->bigIncrements('id') : $table->increments('id');

    }

    public function renewId(): self {
        [$conn, $table] = static::connection();
        $sql    = "SELECT nextval('{$table}_id_seq')";
        $newId  = $conn->select($sql)[0]->nextval;
        $this->setId($newId);
        return $this;
    }

    public static function resetSequence(int $val, string $seqName = null) {
        [$conn, $table] = static::connection();
        !$seqName && $seqName = "{$table}_id_seq";
        $sql = "ALTER SEQUENCE $seqName RESTART WITH $val";
        return $conn->statement($sql);
    }

}