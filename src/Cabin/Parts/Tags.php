<?php

namespace Luclin\Cabin\Parts;

use Lianxue\Tspack\Services;

use Illuminate\Database\Schema\Blueprint;
use DB;

/**
 * @property array $tags
 */
trait Tags
{
    public function setTagsAttribute($value) {
        $this->attributes['tags'] = $this->arrayFieldEncode($value);
        Services\Tag::createByUser(...$this->tags);
    }

    public function getTagsAttribute() {
        return $this->arrayFieldDecode('tags');
    }

    protected static function migrateUpTags(?Blueprint $table): void
    {
        $table  = static::getTablenameWithSchema();
        DB::statement("ALTER TABLE $table ADD COLUMN tags character varying(50)[];");
    }

    protected static function migrateDownTags(?Blueprint $table): void
    {
        $table->dropColumn('tags');
    }
}