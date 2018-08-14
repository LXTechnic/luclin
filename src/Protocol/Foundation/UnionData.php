<?php

namespace Luclin\Protocol\Foundation;

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

    public function __invoke(iterable $slave): void {
        $this->process($slave);
    }

    private function process(iterable $slave): void {
        // 先构建索引
        $index = [];
        foreach ($slave as $rowSlave) {
            foreach ($this->conf as $alias => [$class, $masterField, $slaveField]) {
                if (is_array($rowSlave[$slaveField])) {
                    foreach ($rowSlave[$slaveField] as $unionId) {
                        $index[$masterField][$alias][$unionId][] = $class
                            ? (new $class)->fill($rowSlave) : $rowSlave;
                    }
                    continue;
                }
                $index[$masterField][$alias][$rowSlave[$slaveField]][] = $class
                    ? (new $class)->fill($rowSlave) : $rowSlave;
            }
        }

        // 然后并入
        foreach ($this->lists as $rowMaster) {
            foreach ($index as $masterField => $aliases) {
                foreach ($aliases as $alias => $slaves) {
                    // 加入 master 数组字段支持
                    if (!$rowMaster->$masterField) {
                        continue;
                    }
                    if (is_array($rowMaster->$masterField)) {
                        foreach ($rowMaster->$masterField as $value) {
                            isset($slaves[$value])
                                && $rowMaster->addUnion($alias, ...$slaves[$value]);
                        }
                    } else {
                        isset($slaves[$rowMaster->$masterField])
                            && $rowMaster->addUnion($alias,
                                ...$slaves[$rowMaster->$masterField]);
                    }
                }
            }
        }
    }
}