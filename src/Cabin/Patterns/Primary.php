<?php

namespace Luclin\Cabin\Patterns;

use Luclin\Cabin;
use Luclin\Contracts;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class Primary extends Cabin\Model\Master
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
        $table->softDeletes();

        // 认证与信息
        $table->string('handle', 250)->nullable()->comment('唯一名');
        $table->string('email', 250)->nullable()->comment('邮件');
        $table->string('mobile', 250)->nullable()->comment('电话');
        $table->string('password', 250)->nullable()->comment('密码');
        $table->smallInteger('sex')->nullable()->comment('性别');
        $table->string('location', 250)->nullable()->comment('地址信息');

        // 分类
        $table->smallInteger('type')->nullable()->comment('类型');
        $table->string('category', 250)->nullable()->comment('分类');
        $table->addColumn('_varchar', 'tags', ['nullable' => true])->comment('标签 搜索性质');

        // 上级与关联
        $table->bigInteger('created_by')->nullable()->comment('创建者id');

        // 权限
        $table->addColumn('_integer', 'admins', ['nullable' => true])->comment('可管理者ids');
        $table->string('role', 250)->nullable()->comment('主角色');

        // 状态
        $table->smallInteger('status')->nullable()->comment('状态流记录');
        $table->jsonb('timestamps')->nullable()->comment('多模时间戳记录');
        $table->addColumn('_varchar', 'states', ['nullable' => true])->comment('状态信息 展示性质');

        // 内容
        $table->string('name', 250)->nullable()->comment('名称');
        $table->string('icon', 250)->nullable()->comment('图标');
        $table->text('description')->nullable()->comment('说明');
        $table->string('mark', 250)->nullable()->comment('注释');
        $table->jsonb('details')->nullable()->comment('记录类扩展数据');

        // 支付相关
        $table->bigInteger('price')->nullable()->comment('单价');
        $table->jsonb('prices')->nullable()->comment('多模价格');

        // 计数
        $table->bigInteger('members_count')->nullable()->comment('下属(数据)计数');

        // 扩展信息
        $table->jsonb('extends')->nullable()->comment('业务跟随扩展数据');

        // 索引 ======================================
        $table->unique(['handle']);
    }
}