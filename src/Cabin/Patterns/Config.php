<?php

namespace Luclin\Cabin\Patterns;

use Luclin\Cabin;
use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 配置型数据
 */
abstract class Config extends Cabin\Model\Generated2
    implements Contracts\MigrateUpdate
{
    use SoftDeletes;

    protected static $unguarded = true;

    protected $casts = [
    ];

    public static function migrateUpdate(Blueprint $table, ...$features): void {
        static::migrateUp($table,
            'Primary'
        );
        $table->timestamps();

        // 认证与信息
        $table->string('code', 250)->nullable()->comment('唯一码');
        $table->bigInteger('user_id')->nullable()->comment('所属用户');

        // 分类
        $table->string('major', 250)->nullable();
        $table->string('minor', 250)->nullable();
        $table->addColumn('_varchar', 'tags', ['nullable' => true])->comment('标签 搜索性质');

        // 上级与关联
        $table->bigInteger('created_by')->nullable()->comment('创建者id');
        $table->string('resource_type', 250)->nullable()->comment('关联类型');
        $table->string('resource_id', 250)->nullable()->comment('关联id');

        // 权限
        $table->string('role', 250)->nullable()->comment('主角色');

        // 状态
        $table->boolean('is_actived')->nullable()->comment('是否有效');

        // 内容
        $table->string('name', 250)->nullable()->comment('名称');
        $table->string('icon', 250)->nullable()->comment('图标');
        $table->text('description')->nullable()->comment('说明');
        $table->jsonb('body')->nullable()->comment('主扩展数据');
        $table->addColumn('_varchar', 'flags', ['nullable' => true])->comment('标记 查看性质');

        // 索引 ======================================
        $table->unique(['code']);


    }
}