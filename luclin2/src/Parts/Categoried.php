<?php

namespace Luclin2\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * @property bool $is_actived
 * @property int $type
 * @property string $category
 * @property array $tags
 */
trait Categoried
{
    protected static function migrateUpCategoried(Blueprint $table): void
    {
        $table->boolean('is_actived')->nullable()->comment('是否有效');
        $table->smallInteger('type')->nullable()->comment('类型');
        $table->string('category', 250)->nullable()->comment('分类');
        $table->addColumn('_varchar', 'tags', ['nullable' => true])->comment('标签 搜索性质');
    }

    protected static function migrateDownCategoried(Blueprint $table): void
    {
        $table->dropColumn('is_actived');
        $table->dropColumn('type');
        $table->dropColumn('category');
        $table->dropColumn('tags');
    }

    public function setTagsAttribute($value) {
        $this->attributes['tags'] = $this->arrayFieldEncode($value);
    }

    public function getTagsAttribute() {
        return $this->arrayFieldDecode('tags');
    }

}