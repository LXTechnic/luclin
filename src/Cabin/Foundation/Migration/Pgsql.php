<?php

namespace Luclin\Cabin\Foundation\Migration;

use Luclin\Contracts;
use Luclin\Foundation;
use Luclin\Loader;

use DB;

class Pgsql
{
    const TYPE_MAPPING = [
        'string'        => 'character varying',
        'smallInteger'  => 'smallint',
        'bigInteger'    => 'bigint',
        'float'         => 'double precision',
    ];

    private $class;

    private $stack = [];

    private $modifier = [];

    private $postProcessing = [];//comment on column user.userid is 'The user ID';

    public function __construct(string $class)
    {
        $this->class = $class;
    }

    public function __call(string $type, array $arguments): self {
        $this->stack[] = [
            'ADD COLUMN',
            $type,
            $arguments,
        ];

        return $this;
    }

    public function nullable(): self {
        $this->modifier[count($this->stack) - 1]['nullable'] = true;
        return $this;
    }

    public function default($val): self {
        $this->modifier[count($this->stack) - 1]['default'] = $val;
        return $this;
    }

    public function arrayed(): self {
        $this->modifier[count($this->stack) - 1]['arrayed'] = true;
        return $this;
    }

    public function comment(string $words): self {
        $this->postProcessing[count($this->stack) - 1][] = "COMMENT ON COLUMN {{schema}}.{{table}}.{{field}} IS '$words'";
        return $this;
    }

    public function apply() {
        try {
            // TODO: 这里是有问题的，没有对事实操作的库开启事务。
            DB::beginTransaction();

            $class = $this->class;
            foreach ($this->stack as $id => [$action, $type, $arguments]) {
                $field  = array_shift($arguments);
                isset(self::TYPE_MAPPING[$type]) && $type = self::TYPE_MAPPING[$type];
                [$_, $table, $schema] = $class::connectionInfo();

                $sql    = "ALTER TABLE $schema.$table $action $field $type";

                // 类型参数
                if ($arguments) {
                    $sql .= '('.implode(', ', $arguments).')';
                }

                // 数组
                if (isset($this->modifier[$id]['arrayed'])
                    && $this->modifier[$id]['arrayed'])
                {
                    $sql .= '[]';
                }

                // nullable
                if (isset($this->modifier[$id]['nullable'])
                    && $this->modifier[$id]['nullable'])
                {
                    $sql .= ' NULL';
                } else {
                    $sql .= ' NOT NULL';
                }

                // default
                if (isset($this->modifier[$id]['default']))
                {
                    $sql .= " DEFAULT ".$this->modifier[$id]['default'];
                }
                DB::statement($sql);

                // post processing
                if (isset($this->postProcessing[$id])) {
                    foreach ($this->postProcessing[$id] as $sql) {
                        $sql = \luc\padding($sql, [
                            'schema'    => $schema,
                            'table'     => $table,
                            'field'     => $field,
                        ]);
                        DB::statement($sql);
                    }
                }
            }

            DB::commit();
        } catch (\Throwable $exc) {
            DB::rollback();
            throw $exc;
        }
    }
}
