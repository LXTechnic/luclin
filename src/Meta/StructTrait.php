<?php
namespace Luclin\Meta;

use Luclin\Contracts;

/**
 * 标准Struct基类
 *
 * 一个原则：读取单个动态属性时不判断default结构中是否存在，但写入时限制。
 *
 * deprecated的策略是当单个存取的时候不受影响，但批量读取、处理、填充时将默认跳过。
 *
 * @author andares
 */
trait StructTrait
{
    use Struct\AccessorsTrait,
        Struct\ToArrayTrait,
        Struct\SerializableTrait;

    /**
     * 已弃用字段
     *
     * @var array
     */
    protected static $_deprecated = [];

    protected static $_includeDeprecated = false;

    public static function isDefined(string $key): bool {
        $defaults = static::defaults();
        return array_key_exists($key, $defaults);
    }

    protected static function _defaults(): array {
        return [];
    }

    protected static function _nullable(): ?array {
        return [];
    }

    protected static function _virtuals(): array {
        return [];
    }

    /**
     * 获取基础数据结构
     * @return array
     */
    public static function defaults(): array {
        static $enabled = [];
        if (static::$_includeDeprecated) {
            return static::_defaults();
        }

        if (!$enabled) {
            foreach (static::_defaults() as $key => $default) {
                !static::isKeyDeprecated($key) && $enabled[$key] = $default;
            }
        }
        return $enabled;
    }

    /**
     * 是否为已弃用字段
     *
     * @param int|string $key
     * @return bool
     */
    public static function isKeyDeprecated($key): bool {
        return isset(static::$_deprecated[$key]);
    }

    public function includeDeprecated(callable $fun) {
        static::$_includeDeprecated = true;
        $result = $fun($this);
        static::$_includeDeprecated = false;
        return $result;
    }

    /**
     *
     * @param int|string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null) {
        $value = parent::get($key);
        return $value !== null ? $value
            : ((static::defaults(true)[$key]
                ?? ($this->callVirtualProperty($key) ?: $default)));
    }

    protected function callVirtualProperty(string $key) {
        $virtuals = static::_virtuals();
        if (!isset($virtuals[$key])) {
            return null;
        }
        return $virtuals[$key]->call($this);
    }

    /**
     * 返回一个遍历内部数据的迭代器
     *
     * @return iterable
     */
    public function iterate(): iterable {
        foreach (static::defaults() as $key => $default) {
            yield $key => $this->get($key);
        }
    }

    /**
     *
     * @return Contracts\Meta
     * @throws \UnexpectedValueException
     */
    public function confirm(): Contracts\Meta {
        $nullable = static::_nullable();

        // confirm勾子
        foreach ($this->iterate() as $key => $value) {
            $method = "_confirm_$key";
            if (method_exists($this, $method)) {
                $value = $this->$method($value);
            } elseif ($value === null
                && is_array($nullable)
                && !isset($nullable[$key]))
            {
                throw new \UnexpectedValueException(
                    "meta:".static::class." field [$key] could not be empty");
            }

            // 赋回
            $this->set($key, $value);
        }

        return parent::confirm();
    }

}
