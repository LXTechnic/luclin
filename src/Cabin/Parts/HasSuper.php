<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

use Lianxue\Tspack\Services;

/**
 * @property int $super_type
 * @property int $super_id
 */
trait HasSuper
{
    public function setSuper(int $type, int $id): self {
        $this->super_type   = $type;
        $this->super_id     = $id;
        return $this;
    }

    public function getSuper() {
        return Services\Resource::get($this->super_type, $this->super_id);
    }

    public function inheritSuper($same): self {
        $this->super_type   = $same->super_type;
        $this->super_id     = $same->super_id;
        return $this;
    }

    protected static function migrateUpHasSuper(Blueprint $table): void
    {
        $table->smallInteger('super_type')->nullable();
        $table->bigInteger('super_id')->nullable();
    }

    protected static function migrateDownHasSuper(Blueprint $table): void
    {
        $table->dropColumn('super_type');
        $table->dropColumn('super_id');
    }
}