<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property int $super_type
 * @property int $super_id
 */
trait HasSuper
{
    abstract public function getSuper();

    protected static function migrateUpHasSuper(Blueprint $table): void
    {
        $table->string('super_type', 50)->nullable();
        $table->string('super_id', 250)->nullable();
    }

    protected static function migrateDownHasSuper(Blueprint $table): void
    {
        $table->dropColumn('super_type');
        $table->dropColumn('super_id');
    }

    public function setSuper($type, $id): self {
        $this->super_type   = $type;
        $this->super_id     = $id;
        return $this;
    }

    public function inheritSuper($same): self {
        $this->super_type   = $same->super_type;
        $this->super_id     = $same->super_id;
        return $this;
    }
}