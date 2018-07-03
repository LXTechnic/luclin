<?php

namespace Luclin\Cabin\Parts;

use Lianxue\Foundation\Resource;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property int $master_type
 * @property int $master_id
 */
trait HasMaster
{
    protected static function migrateUpHasMaster(Blueprint $table): void
    {
        $table->smallInteger('master_type')->nullable();
        $table->string('master_id', 50)->nullable();
    }

    protected static function migrateDownHasMaster(Blueprint $table): void
    {
        $table->dropColumn('master_type');
        $table->dropColumn('master_id');
    }

    public function setMaster(object $master) {
        $this->master_type  = Resource::getTypeByInstance($master);
        $this->master_id    = $master->getId();
    }

    public function getMaster(): object {
        return Resource::get($this->master_type, $this->master_id);
    }
}