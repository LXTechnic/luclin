<?php

namespace Luclin\Cabin\Foundation;

use Luclin\Contracts;
use Luclin\Cabin;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;

use DB;

// TODO: 需要在之后加入对find/findMany以及whereKey的支持

abstract class Model extends EloquentModel implements Contracts\Model
{

    protected static $connectionInfo = null;

    protected $_onFactory = false;

    public function setOnFactoryAttribute($value) {
        $this->_onFactory = $value;
    }

    public static function find($id, bool $reload = false) {
        if (is_array($id) || $id instanceof Arrayable) {
            return static::findMany($id);
        }

        return static::whereKey($id)->first();
    }

    public function id() {
        $key = is_array($this->primaryKey) ? static::getIdField() : $this->primaryKey;
        return $this->{$key};
    }

    public function setId($id): self {
        $this->{$this->primaryKey} = $id;
        return $this;
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
            $fun   = array_shift($ids);
            $query  = $fun(self::whereIn(static::getIdField(), $ids));
        } else {
            $query  = self::whereIn(static::getIdField(), $ids);
        }
        return $query->get();
    }

    protected static function getIdField(): string {
        return 'id';
    }

    public function fillWithMapping(iterable $data, array $mapping = []) {
        foreach ($mapping as $from => $to) {
            if (isset($data[$from])) {
                $data[$to] = $data[$from];
                unset($data[$from]);
            }
        }
        return $this->fill($data);
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

    public static function connectionInfo(): array {
        $model  = new static();
        $connection = $model->getConnectionName();
        $schema = config("database.connections.$connection.schema");
        $table  = $model->getTable();
        return [$connection, $table, $schema];
    }

    public static function connection(): array {
        [$connection, $table, $schema] = static::connectionInfo();
        return [
            static::resolveConnection($connection),
            $schema ? "$schema.$table" : $table,
        ];
    }

    public function getSchema(): string {
        $connection = $this->getConnectionName();
        return config("database.connections.$connection.schema");
    }

    public function self(): Builder {
        $query = $this->newQuery();

        if (!$this->exists) {
            throw new \RuntimeException("model is not exists, could not call method self().");
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

    public static function countLimit(Builder $query,
        int $limit, string $field = 'id'): int
    {
        [$connection, $table, $schema] = static::connectionInfo();
        $connection = static::resolveConnection($connection);

        $query->getQuery()->limit   = $limit;
        $query->getQuery()->orders  = [];
        $sql = 'SELECT count(*) AS total FROM ('.$query->toSql().') AS a';
        $result = $connection->select($sql, $query->getBindings());
        return isset($result[0]) ? $result[0]->total : 0;
    }

    public static function estimateLiveRows(): int {
        [$connection, $table, $schema] = static::connectionInfo();
        $connection = static::resolveConnection($connection);

        $sql = "SELECT n_live_tup::int AS total FROM pg_stat_all_tables WHERE relname = '$table' AND schemaname = '$schema'";
        $result = $connection->select($sql);
        return isset($result[0]) ? $result[0]->total : 0;
    }

    public function reloadAttributes(...$attributes): self {
        $reload = self::find($this->id());
        foreach ($attributes as $attribute) {
            $this->$attribute = $reload->$attribute;
            $this->syncOriginalAttribute($attribute);
        }
        return $this;
    }

    public function updateJson(string $field, array $path, string $value) {
        // return $this->self()->update(DB::Raw("$field = jsonb_set($field, '{".implode(',', $path)."}', '$value')"));

        [$conn, $table] = static::connection();

        $sql = "UPDATE $table SET $field = jsonb_set($field, '{".implode(',', $path)."}', '$value') WHERE ".$this->getKeyName()." = ".$this->getKey();
        return $conn->update($sql);
    }

    public function incrementMore(array $values, array $update = [])
    {
        $query   = $this->self()->toBase();
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

    public static function foundByIds(...$ids): array {
        $pkey   = (new static())->primaryKey;
        $tmp    = self::whereIn($pkey, $ids)->get()->keyBy($pkey);
        $result = [];
        foreach ($ids as $id) {
            if (isset($tmp[$id])) {
                $result[] = $tmp[$id];
            } else {
                $model = new static();
                $model->$pkey = $id;
                $result[] = $model;
            }
        }
        return $result;
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