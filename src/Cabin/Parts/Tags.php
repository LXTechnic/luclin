<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property array $tags
 */
trait Tags
{
    public function setTagsAttribute($value) {
        $this->attributes['tags'] = $this->arrayFieldEncode($value);
    }

    public function getTagsAttribute() {
        return $this->arrayFieldDecode('tags');
    }

    protected static function migrateUpTags(?Blueprint $table): void
    {
        [$conn, $table] = static::connection();
        $conn->statement("ALTER TABLE $table ADD COLUMN tags character varying(50)[]");
    }

    protected static function migrateDownTags(?Blueprint $table): void
    {
        $table->dropColumn('tags');
    }
}