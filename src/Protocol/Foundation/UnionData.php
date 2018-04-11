<?php

namespace Luclin\Protocol\Foundation;

use Luclin\MetaInterface;
use Luclin\Meta\Collection;
use Luclin\Protocol\Lists;

class UnionData
{
    private $lists;

    private $conf;

    private $index = [];

    public function __construct(Lists $lists, array $conf) {
        $this->lists = $lists;
        $this->conf  = $conf;
    }

    public function __invoke(array $slave): void {
        $this->process($slave);
    }

    private function process(array $slave): void {
        // 先构建索引
        $index = [];
        foreach ($slave as $row) {
            foreach ($this->conf as $alias => [$class, $masterField, $slaveField]) {
                if (is_array($row[$slaveField])) {
                    foreach ($row[$slaveField] as $unionId) {
                        $index[$masterField][$alias][$unionId][] = $class
                            ? (new $class)->fill($row) : $row;
                    }
                    continue;
                }
                $index[$masterField][$alias][$row[$slaveField]][] = $class
                    ? (new $class)->fill($row) : $row;
            }
        }

        // 然后并入
        foreach ($this->lists as $row) {
            foreach ($index as $masterField => $aliases) {
                foreach ($aliases as $alias => $slaves) {
                    // 加入 master 数组字段支持
                    if (!$row[$masterField]) {
                        continue;
                    }
                    if (is_array($row[$masterField])) {
                        foreach ($row[$masterField] as $value) {
                            isset($slaves[$value])
                                && $row->addUnion($alias, ...$slaves[$value]);
                        }
                    } else {
                        isset($slaves[$row[$masterField]])
                            && $row->addUnion($alias, ...$slaves[$row[$masterField]]);
                    }
                }
            }
        }
    }
}