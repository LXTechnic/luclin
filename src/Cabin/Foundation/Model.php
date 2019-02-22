<?php

namespace Luclin\Cabin\Foundation;

use Luclin\Contracts;
use Luclin\Cabin;

use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Schema\Blueprint;

use DB;

/**
 *
 *
 * DB::statement("ALTER TABLE main.castle_user ALTER COLUMN roles SET DEFAULT '{member}';");
 */
abstract class Model extends EloquentModel implements Contracts\Model
{

    protected static $connectionInfo = null;

    protected $_onFactory = false;

    protected $_newBindId = null;

    public function setOnFactoryAttribute($value) {
        $this->_onFactory = $value;
    }

    public function setNewBindId(string $id): self {
        $this->_newBindId = $id;
        return $this;
    }

    public static function find($id, bool $reload = false, array $extra = []) {
        $result = Cabin::load(static::class, $id, function(array $id) use ($extra) {
            // TODO: 注意这里目前对复写了findMany()方法的Slave基类不起作用
            if ($extra['withTrashed'] ?? false) {
                return static::withTrashed()->findMany($id);
            } elseif ($extra['withoutTrashed'] ?? false) {
                return static::withoutTrashed()->findMany($id);
            } elseif ($extra['onlyTrashed'] ?? false) {
                return static::onlyTrashed()->findMany($id);
            }
            return static::findMany($id);
        }, $reload);

        if (is_array($id) && !is_iterable($result)) {
            return new Collection([$result]);
        }
        return $result;
    }

    public static function findOrFail($id, bool $reload = false, array $extra = []) {
        $result = static::find($id, $reload, $extra);

        if (is_array($id)) {
            if (count($result) === count(array_unique($id))) {
                return $result;
            }
        } elseif (! is_null($result)) {
            return $result;
        }

        static::orFail(null, $id);
    }

    public static function orFail($model, $info = 0, string $abort = null): object {
        if (!$model) {
            if ($abort) {
                throw \luc\raise($abort, $info ? (is_array($info) ? $info : ['info' => $info]) : []);
            } else {
                throw (new ModelNotFoundException)->setModel(
                    static::class, $info
                );
            }
        }
        return $model;
    }

    public static function found($feature, bool $reload = false, array $extra = []) {
        return Cabin::loadByFeatures(static::class, $feature, function($feature) use ($extra) {
            if (is_array($feature)) {
                if ($extra['withTrashed'] ?? false) {
                    $model  = static::withTrashed()->firstOrNew($feature);
                } elseif ($extra['withoutTrashed'] ?? false) {
                    $model  = static::withoutTrashed()->firstOrNew($feature);
                } elseif ($extra['onlyTrashed'] ?? false) {
                    $model  = static::onlyTrashed()->firstOrNew($feature);
                } else {
                    $model  = static::firstOrNew($feature);
                }

            } else {
                if ($extra['withTrashed'] ?? false) {
                    $model  = static::withTrashed()->findOrNew($feature);
                } elseif ($extra['withoutTrashed'] ?? false) {
                    $model  = static::withoutTrashed()->findOrNew($feature);
                } elseif ($extra['onlyTrashed'] ?? false) {
                    $model  = static::onlyTrashed()->findOrNew($feature);
                } else {
                    $model  = static::findOrNew($feature);
                }
            }

            return $model;
        }, $reload);
    }

    public static function foundOrNull($feature, bool $reload = false, array $extra = []) {
        $model = static::found($feature, $reload, $extra);
        return $model->exists ? $model : null;
    }

    public function id() {
        $key = is_array($this->primaryKey) ? static::getIdField() : $this->primaryKey;
        return array_key_exists($key, $this->attributes) ? $this->{$key} : null;
    }

    public function findId() {
        return $this->id();
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

    public static function queryByIds(array $ids): Builder {
        return static::whereIn(static::getIdField(), $ids);
    }

    /**
     * TODO: 以后这些查询强化要设法加入到query对象中
     */
    public static function getByIds(...$ids): Collection {
        $query = static::queryByIds($ids);
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

    public static function cabin(): Cabin\Container {
        return Cabin\Container::instance(static::class);
    }

    public function confirm(): self {
        // 整体confirm勾子
        $methods = $this->afterConfirm();
        if ($methods && is_array($methods)) foreach ($methods as $method) {
            $call = [$this, $method];
            $call();
        }
        return $this;
    }

    protected function afterConfirm() {}

    public static function migrate(): object {
        $class = __NAMESPACE__.'\\Migration\\'.ucfirst((new static())->getDriver());
        return new $class(static::class);
    }

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

    public function getDriver(): string {
        $connection = $this->getConnectionName();
        return config("database.connections.$connection.driver");
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

    public function getExtra(string $class): object {
        $model = $class::found([
            'id'    => $this->id(),
        ]);
        return $model;
    }

    /**
     *
     * // return $this->self()->update(DB::Raw("$field = jsonb_set($field, '{".implode(',', $path)."}', '$value')"));
     *
     * @param string $field
     * @param array $path
     * @param string $value
     * @return void
     */
    public function updateJson(string $field, array $path, string $value) {
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

    public function toCabin(): self {
        $this->cabin()[$this->findId()] = $this;
    }

    public function save(array $options = []) {
        $this->confirm();

        $isCreated = !$this->exists;

        $result = parent::save($options);
        if (!$result) {
            return $result;
        }

        // 处理newBindId
        if ($this->_newBindId) {
            $this->cabin()->transformNewModel($this->_newBindId);
            $this->_newBindId = null;
        } elseif ($isCreated) {
            $this->cabin()[$this->findId()] = $this;
        }
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
        foreach ($value ?: [] as $k => $v) {
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