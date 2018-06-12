<?php

namespace Luclin;

use Luclin\Contracts;
use Luclin\Flow;

use DB;
use Log;

/**
 */
abstract class Domain
{
    use Foundation\SingletonTrait;

    protected $_functionIndex = [];
    protected $_providerCache = null;

    protected static function _provides(): array {
        return [
            Domain\Providers\Common::class,
            static::class.'\\Actions',
            static::class.'\\Inspectors',
        ];
    }

    public function id(): string {
        return static::class;
    }

    public function __call(string $name, array $arguments) {
        [$class, $method] = $this->getProviderClass(
                Domain\Provider::TYPE_ACTION, $name);
        if (!$class) {
            throw new \RuntimeException("The action $name is not exists.");
        }
        return $class::instance()
            ->setDomain($this)
            ->setContext(array_shift($arguments))
            ->$method(...$arguments);
    }

    public function inspect(string $name,
        Flow\Context $context, Flow\When $when): bool
    {
        [$class, $method] = $this->getProviderClass(
                Domain\Provider::TYPE_INSPECTOR, $name);
        if (!$class) {
            throw new \RuntimeException("The inspect $name is not exists.");
        }
        return $class::instance()
            ->setDomain($this)
            ->setContext($context)
            ->$method($when);
    }

    public function asRole($object, string $role) {
        if (!is_object($object) || !method_exists($object, 'assume')) {
            return $object;
        }
        $class = $this->getRoleClass($role);
        if (class_exists($class)) {
            $object->assume($role, new $class());
        }
        return $object;
    }

    protected function getProviderClass(int $type, string $name): array {
        if (!isset($this->_functionIndex[$type][$name])) {
            !$this->_providerCache && $this->_providerCache = static::_provides();
            foreach ($this->_providerCache as $class) {
                if ($class::$type !== $type) {
                    continue;
                }
                if (method_exists($class, $name)) {
                    $this->_functionIndex[$type][$name] = [$class, $name];
                    break;
                }
            }
        }
        return $this->_functionIndex[$type][$name] ?? [null, null];
    }

    protected function getRoleClass(string $name): string {
        return static::class.'\\Roles\\'.strtr(ucwords($name, '/'), '/', '\\');
    }

}
