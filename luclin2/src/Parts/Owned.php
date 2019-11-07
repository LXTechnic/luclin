<?php

namespace Luclin2\Parts;

use Illuminate\Database\Schema\Blueprint;


/**
 * @property int $user_id
 */
trait Owned
{
    protected static function migrateUpOwned(Blueprint $table): void
    {
        $f = $table->bigInteger('user_id')->default(0);
    }

    protected static function migrateDownOwned(Blueprint $table): void
    {
        $table->dropColumn('user_id');
    }

    public function getOwner(): ?Models\User {
        return \luc\imp(__NAMESPACE__.":getOwner", $this->user_id);
    }

    public function isOwner($owner): bool {
        $id = is_object($owner) ? $owner->id() : $owner;
        return $this->user_id == $id;
    }

}