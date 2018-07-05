<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property int $master_type
 * @property int $master_id
 */
trait HasMaster
{
    abstract public function getMaster();

    protected static function migrateUpHasMaster(Blueprint $table): void
    {
        $table->string('master_type', 50)->nullable();
        $table->string('master_id', 250)->nullable();
    }

    protected static function migrateDownHasMaster(Blueprint $table): void
    {
        $table->dropColumn('master_type');
        $table->dropColumn('master_id');
    }

    public function setMaster($type, $id): self {
        $this->master_type  = $type;
        $this->master_id    = $id;
        return $this;
    }
}