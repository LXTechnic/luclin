<?php

namespace Luclin\Cabin\Model;

use Luclin\Foundation\{
    Model as FoundationModel,
    RolableTrait
};
use Luclin\Cabin;
use Luclin\Cabin\Foundation;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use DB;

class Master extends FoundationModel
{
    use RolableTrait;

    public function getId() {
        $key = is_array($this->primaryKey) ? static::getIdField() : $this->primaryKey;
        return $this->{$key};
    }

    public static function sortByIds(...$ids): Collection {
        $result = static::getByIds(...$ids)->keyBy(static::getIdField());
        isset($ids[0]) && is_callable($ids[0]) && array_shift($ids);

        $collection = new Collection();
        foreach ($ids as $id) {
            isset($result[$id]) && $collection->push($result[$id]);
        }
        return $collection;
    }

    /**
     * TODO: 以后这些查询强化要设法加入到query对象中
     */
    public static function getByIds(...$ids): Collection {
        if (isset($ids[0]) && is_callable($ids[0])) {
            $func   = array_shift($ids);
            $query  = $func(self::whereIn(static::getIdField(), $ids));
        } else {
            $query  = self::whereIn(static::getIdField(), $ids);
        }
        return $query->get();
    }

    protected static function getIdField(): string {
        return 'id';
    }

    // public static function f($id) {
    //     return Cabin::load(static::class, $id);
    // }

    // public static function fof($id) {
    //     return Cabin::load(static::class, $id, true);
    // }

    // public static function fofWithTrashed($id) {
    //     return Cabin::load(static::class, $id, true, true);
    // }

    public static function contains(string $field, ...$values): Builder {
        return self::query()
            ->whereRaw("$field @> '{".implode(',', $values)."}'");
    }

    public static function notContains(string $field, ...$values): Builder {
        return self::query()
            ->whereRaw("not ($field && '{".implode(',', $values)."}')");
    }

    public static function congruent(string $field, ...$values): Builder {
        return self::query()
            ->whereRaw("$field = '{".implode(',', $values)."}'");
    }

    public function confirm(): self {
        // 整体confirm勾子
        $methods = $this->afterConfirm();
        if ($methods && is_array($methods)) {
            foreach ($methods as $method) {
                $call = [$this, $method];
                $call();
            }
        }
        return $this;
    }

    protected function afterConfirm() {}

    public function getSchema(): string {
        $connection = $this->getConnection()->getName();
        return config("database.connections.$connection.schema");
    }

    public static function getTablenameWithSchema(): string {
        $model = new static();
        return $model->getSchema().'.'.$model->getTable();
    }

    public function own(): Builder {
        $query = $this->newQuery();

        if (!$this->exists) {
            throw new \RuntimeException("model is not exists, could not call method own().");
        }

        $key = $this->getKeyName();
        if (is_array($key)) {
            foreach ($key as $pk) {
                $query->where($pk, $this->$pk);
            }
            return $query;
        }

        return $query->where(
            $this->getKeyName(), $this->getKey()
        );
    }

    public function reloadAttributes(...$attributes): self {
        $reload = self::find($this->getId());
        foreach ($attributes as $attribute) {
            $this->$attribute = $reload->$attribute;
            $this->syncOriginalAttribute($attribute);
        }
        return $this;
    }

    public function reload(): self {
        return $this->fill(self::find($this->getId())->toArray())
            ->syncOriginal();
    }

    public function updateJson(string $field, array $path, string $value) {
        // return $this->own()->update(DB::Raw("$field = jsonb_set($field, '{".implode(',', $path)."}', '$value')"));

        $sql = "UPDATE ".static::getTablenameWithSchema()." SET $field = jsonb_set($field, '{".implode(',', $path)."}', '$value') WHERE ".$this->getKeyName()." = ".$this->getKey();
        return DB::update($sql);
    }

    public function incrementMore(array $values, array $update = [])
    {
        $query   = $this->own()->toBase();
        $grammar = $query->getGrammar();
        foreach ($values as $field => $value) {
            $update[$field] = DB::Raw($grammar->wrap($field)." + $value");
        }
        return $query->update($update);
    }

    public function save(array $options = []) {
        $this->confirm();
        return parent::save($options);
    }

// array field access

    protected function arrayFieldDecode(string $field): array
    {
        $value  = $this->attributes[$field] ?? null;
        $value && $value = substr($value, 1, -1);
        $value  = $value ? str_getcsv($value) : [];
        $result = [];
        foreach ($value as $unit) {
            $unit = $this->castAttribute($field, $unit);
            $result[] = $unit;
        }
        return $result;
    }

    protected function arrayFieldEncode($value): string
    {
        // 去一下空字符
        $data = [];
        foreach ($value as $k => $v) {
            $v = trim($v);
            $v && $data[] = $v;
        }
        if (!$data) {
            return '{}';
        }

        $limit  = 4 * 1024;
        $handle = fopen("php://temp/maxmemory:$limit", 'r+');
        fputcsv($handle, $data);
        rewind($handle);
        $data = trim(stream_get_contents($handle));
        fclose($handle);
        $result = '{'.$data.'}';
        return $result;
    }


// parted
    public static function migrateUp(?Blueprint $table, ...$parts): void {
        static::migrateProcess(true, $table, $parts);
    }

    public static function migrateDown(?Blueprint $table, ...$parts): void {
        static::migrateProcess(false, $table, $parts);
    }

    protected static function migrateProcess(bool $mode,
        ?Blueprint $table, array $parts): void
    {
        $methodPrefix = $mode ? 'migrateUp' : 'migrateDown';
        foreach ($parts as $part) {
            if (is_array($part)) {
                $call = [static::class, $methodPrefix.ucfirst(array_shift($part))];
                $call($table, ...$part);
            } else {
                $call = [static::class, $methodPrefix.ucfirst($part)];
                $call($table);
            }
        }
    }

}