<?php

namespace Luclin\Cabin\Patterns;

use Luclin\Cabin;
use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 多值类，多用于属性保存与统计
 */
abstract class Values extends Cabin\Model\Generated2
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

        // 计数
        $table->bigInteger('amount')->nullable()->comment('金额');
        $table->bigInteger('quantity')->nullable()->comment('数量');
        $table->bigInteger('remain')->nullable()->comment('剩余次数');
        $table->bigInteger('total')->nullable()->comment('总共次数');
        $table->bigInteger('members_count')->nullable()->comment('下属(数据)计数');
        $table->bigInteger('success_count')->nullable()->comment('成功计数');
        $table->bigInteger('fail_count')->nullable()->comment('失败计数');

    }
}