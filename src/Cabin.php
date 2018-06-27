<?php

namespace Luclin;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class Cabin
{
    private $cache = [];
    private $cacheLimit = 2000;

    public function cleanCache() {
        $this->cache = [];
    }

    /**
     * Undocumented function
     *
     * @param string $class
     * @param mixed $id
     * @param boolean $autoFail
     * @param boolean $withTrash 已删除的被缓存后再命中的问题暂不处理
     * @return void
     */
    public function load(string $class, $id,
        bool $autoFail = false, bool $withTrashed = false)
    {
        !isset($this->cache[$class]) && $this->cache[$class] = [];

        // 缓存上限清理
        if (count($this->cache[$class]) > $this->cacheLimit) {
            $count = 0;
            foreach ($this->cache[$class] as $flushId => $model) {
                unset($this->cache[$class][$flushId]);
                $count++;
                if ($count > ($this->cacheLimit / 2)) {
                    break;
                }
            }
        }

        if (!array_key_exists($id, $this->cache[$class])) {
            if ($withTrashed) {
                $this->cache[$class][$id] = $autoFail
                    ? $class::withTrashed()->findOrFail($id)
                        : $class::withTrashed()->find($id);
            } else {
                $this->cache[$class][$id] = $autoFail
                    ? $class::findOrFail($id) : $class::find($id);
            }
        }

        $model = $this->cache[$class][$id];
        if ($autoFail && $model == null) {
            throw (new ModelNotFoundException)->setModel(
                $class, $id
            );
        }
        return $model;
    }
}