<?php

namespace Luclin\Cabin\Patterns;

use Luclin\Cabin;
use Luclin\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * 资产类数据，主打多维度
 */
abstract class Property extends Cabin\Model\Generated2
    implements Contracts\MigrateUpdate
{
    use SoftDeletes;

    protected static $unguarded = true;

    protected $casts = [
        'timestamps'    => 'array',
        'body'          => 'array',
        'details'       => 'array',
        'vars'          => 'array',
        'extends'       => 'array',
        'hub'           => 'string',
        'tags'          => 'string',
        'parents'       => 'string',
        'pictures'      => 'string',
        'flags'         => 'string',
        'roles'         => 'string',
        'states'        => 'string',
        'hooks'         => 'string',
    ];

    public static function migrateUpdate(Blueprint $table, ...$features): void {
        if (!$features) {
            static::migrateUp($table,
                'Primary'
            );
            $table->timestamps();
            $table->softDeletes();

            // 认证与信息
            $table->string('code', 250)->nullable()->comment('唯一码');
            $table->bigInteger('user_id')->nullable()->comment('所属用户');

            // 分类
            $table->smallInteger('type')->nullable()->comment('类型');
            $table->string('category', 250)->nullable()->comment('分类');
            $table->addColumn('_varchar', 'hub', ['nullable' => true])->comment('信息分组');
            $table->addColumn('_varchar', 'tags', ['nullable' => true])->comment('标签 搜索性质');

            // 上级与关联
            $table->string('super_id', 250)->nullable()->comment('顶层所属关联');
            $table->string('master_id', 250)->nullable()->comment('上级所属关联');
            $table->addColumn('_varchar', 'parents', ['nullable' => true])->comment('层级或路径');
            $table->bigInteger('created_by')->nullable()->comment('创建者id');
            $table->string('resource_type', 250)->nullable()->comment('关联类型');
            $table->string('resource_id', 250)->nullable()->comment('关联id');

            // 状态
            $table->smallInteger('status')->nullable()->comment('状态流记录');
            $table->boolean('is_actived')->nullable()->comment('是否有效');
            $table->dateTime('active_time')->nullable()->comment('激活时间');
            $table->dateTime('expire_time')->nullable()->comment('过期时间');

            // 内容
            $table->string('name', 250)->nullable()->comment('名称');
            $table->string('icon', 250)->nullable()->comment('图标');
            $table->text('description')->nullable()->comment('说明');
            $table->text('content')->nullable()->comment('正文内容');
            $table->string('mark', 250)->nullable()->comment('注释');
            $table->addColumn('_varchar', 'pictures', ['nullable' => true])->comment('图片列表');
            $table->jsonb('body')->nullable()->comment('主扩展数据');
            $table->jsonb('details')->nullable()->comment('记录类扩展数据');
            $table->addColumn('_varchar', 'flags', ['nullable' => true])->comment('标记 查看性质');

            // 索引 ======================================
            $table->unique(['code']);
        }

        if (in_array('role', $features)) {
            // 权限
            $table->string('role', 250)->nullable()->comment('主角色');
            $table->addColumn('_varchar', 'roles', ['nullable' => true])->comment('拥有角色');
        }

        if (in_array('multi', $features)) {
            // 状态plus
            $table->jsonb('timestamps')->nullable()->comment('多模时间戳记录');
            $table->addColumn('_varchar', 'states', ['nullable' => true])->comment('状态信息 展示性质');
            $table->boolean('is_latest')->nullable()->comment('是否为最新一个');
            $table->integer('sequence')->nullable()->comment('序列/页/版本');
            $table->integer('cursor')->nullable()->comment('当前进度');
        }

        if (in_array('pay', $features)) {
            // 支付相关
            $table->boolean('is_free')->nullable()->comment('是否免费');
            $table->bigInteger('price')->nullable()->comment('单价');
        }

        if (in_array('member', $features)) {
            // 计数
            $table->bigInteger('members_count')->nullable()->comment('下属(数据)计数');
        }

        if (in_array('hook', $features)) {
            // hooks
            $table->jsonb('vars')->nullable()->comment('替换类扩展数据');
            $table->addColumn('_varchar', 'hooks', ['nullable' => true])->comment('勾子');
        }

        if (in_array('extend', $features)) {
            // 扩展信息
            $table->jsonb('extends')->nullable()->comment('业务跟随扩展数据');
        }

    }

    public function setHubAttribute($value) {
        $this->attributes['hub'] = $this->arrayFieldEncode($value);
    }

    public function getHubAttribute() {
        return $this->arrayFieldDecode('hub');
    }

    public function setTagsAttribute($value) {
        $this->attributes['tags'] = $this->arrayFieldEncode($value);
    }

    public function getTagsAttribute() {
        return $this->arrayFieldDecode('tags');
    }

    public function setParentsAttribute($value) {
        $this->attributes['parents'] = $this->arrayFieldEncode($value);
    }

    public function getParentsAttribute() {
        return $this->arrayFieldDecode('parents');
    }

    public function setPicturesAttribute($value) {
        $this->attributes['pictures'] = $this->arrayFieldEncode($value);
    }

    public function getPicturesAttribute() {
        return $this->arrayFieldDecode('pictures');
    }

    public function setFlagsAttribute($value) {
        $this->attributes['flags'] = $this->arrayFieldEncode($value);
    }

    public function getFlagsAttribute() {
        return $this->arrayFieldDecode('flags');
    }

    public function setRolesAttribute($value) {
        $this->attributes['roles'] = $this->arrayFieldEncode($value);
    }

    public function getRolesAttribute() {
        return $this->arrayFieldDecode('roles');
    }

    public function setStatesAttribute($value) {
        $this->attributes['states'] = $this->arrayFieldEncode($value);
    }

    public function getStatesAttribute() {
        return $this->arrayFieldDecode('states');
    }

    public function setHooksAttribute($value) {
        $this->attributes['hooks'] = $this->arrayFieldEncode($value);
    }

    public function getHooksAttribute() {
        return $this->arrayFieldDecode('hooks');
    }

}