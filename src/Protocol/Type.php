<?php

namespace Luclin\Protocol;

use Luclin\MetaInterface;
use Luclin\Meta\Struct;

abstract class Type extends Struct implements FieldInterface
{
    use Foundation\ContrableTrait,
        Foundation\DecorableTrait;

    protected static $_type = "unamed";

    protected static $_id = 'id';

    protected static $_mapping = [];

    protected static $_union = [];

    protected $_raw = null;

    public function __construct($data = null) {
        $data && $this->fill($data);
        $this->setContract('type', static::$_type);
    }

    public function raw() {
        return $this->_raw;
    }

    public function fill($data): MetaInterface {
        is_object($data) && $this->_raw = $data;
        if (is_object($data) && method_exists($data, 'toArray')) {
            $data = $data->toArray();
        }

        static::$_id && isset($data[static::$_id])
            && $this->setId($data[static::$_id]);

        foreach (static::$_mapping as $field => $property) {
            isset($data[$field]) && $data[$property] = $data[$field];
        }

        return parent::fill($data);
    }

    public function id() {
        return $this->getContract('id') ?: 0;
    }

    public function setId($id): self {
        $this->setContract('id', $id);
        return $this;
    }

    public function type(): string {
        return static::$_type;
    }

    public function addUnion(string $alias, ...$dataList): self {
        foreach ($dataList as $data) {
            $this->addContract('union', $data, $alias);
        }
        return $this;
    }

    public function getUnion(string $alias = null): array {
        return $alias ? ($this->getContract('union')[$alias] ?? [])
            : ($this->getContract('union') ?? []);
    }

    public function restore(array $customs = null): self {
        return $this->fill($customs
            ? array_merge($this->defaults(), $customs) : $this->defaults());
    }

    public static function lists($master): Lists {
        return new Lists(static::class, $master, static::$_union);
    }

}