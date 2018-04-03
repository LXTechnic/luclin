<?php

namespace Luclin;

use Luclin\Contracts;

/**
 * TODO: 增加支持Data无属性序列化
 */
abstract class Context extends Meta\Collection
    implements Contracts\Context
{
    protected static $_autoInheritRoles = [];

    protected $_parent  = null;

    protected $_children = [];

    protected $_domain = null;

    public static function at(string $domainClass, ...$arguments) {
        return (new static(...$arguments))->setDomain($domainClass::instance());
    }

    public function setDomain(Domain $domain): self {
        $this->_domain = $domain;
        return $this;
    }

    public function domain(): ?Domain {
        return $this->_domain;
    }

    public function __invoke() {
        $this->verify();
        return $this->handle();
    }

    public function assign($role, ...$arguments): self {
        // 支持作为简单参数赋入
        if (is_array($role)) {
            foreach ($role as $key => $value) {
                $this->$key = $value;
            }
            return $this;
        }

        // 正式赋role
        if (is_string($role)) {
            $alias = $role;
            // 找到对应的role类并创建
            if ($domain = $this->domain()) {
                $roleClass      = $domain->getRoleClass($role);
                $roleInstance   = new $roleClass(...$arguments);
            } else {
                $roleClass      = static::class.'\\'.ucfirst($role).'Role';
                $roleInstance   = new $roleClass(...$arguments);
            }
        } else {
            // 这是作为已经存在的role对象赋入，仅进行提出alias的尝试操作
            if ($role->alias) {
                $alias = $role->alias;
            } else {
                $class  = get_class($role);
                $pos    = strrpos($class, '\\');
                $alias  = str_replace('role', '',
                    strtolower($pos ? (substr($class, $pos + 1)) : $class));
            }
            $roleInstance = $role;
        }

        $this->$alias = $roleInstance;
        return $this;
    }

    protected function nest(string $contextClass, ...$arguments): self {
        $this->_children[] = $context = new $contextClass(...$arguments);
        return $context->createdBy($this);
    }

    public function createdBy(self $parent): self {
        $this->_parent = $parent;
        $this->assign($parent->toArray());
        static::$_autoInheritRoles && $this->inheritRoles($parent, ...static::$_autoInheritRoles);
        return $this;
    }

    public function inheritRoles(self $context, ...$roles): self {
        foreach ($roles as $role) {
            $this->assign($role, $context->$role);
        }

        return $this;
    }

    protected function verify(): void {
    }
}
