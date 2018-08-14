<?php

namespace Luclin;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Cabin
{
    const LOADED_HOLDER = -1;

    public static function load(string $class, $ids,
        callable $loader, bool $reload = false)
    {
        $container = Cabin\Container::instance($class);
        !is_array($ids) && $ids = [$ids];

        // 强制重载
        if ($reload) {
            foreach ($ids as $id) {
                unset($container[$id]);
            }
        }

        // 先从缓存读取
        $needLoadIds    = [];
        $result = [];
        $count  = -1;
        foreach ($ids as $id) {
            $count++;
            if ($container->has($id)) {
                if (is_object($container[$id])
                    || $container[$id] != self::LOADED_HOLDER)
                {
                    $result[$count] = $container[$id];
                    continue;
                }
            } else {
                $needLoadIds[$id]   = $count;
            }
            $result[$count] = null;
        }

        // 从数据库读取
        if ($needLoadIds) {
            $index = [];
            foreach ($loader(array_keys($needLoadIds)) as $model) {
                $index[$model->findId()] = $model;
            }
            foreach ($needLoadIds as $id => $pos) {
                if (isset($index[$id])) {
                    $result[$pos] = $container[$id] = $index[$id];
                    continue;
                }
                $container[$id] = self::LOADED_HOLDER;
            }
        }

        return isset($result[1]) ? new Collection($result) : $result[0];
    }

    public static function loadByFeatures(string $class, $feature,
        callable $loader, bool $reload = false)
    {
        $container = Cabin\Container::instance($class);

        if ($reload || !($model = $container->index($feature))) {
            $model  = $loader($feature);
            if ($model->exists) {
                if (!$reload && $container->has($model->findId())) {
                    // 沿用旧数据
                    $model = $container[$model->findId()];
                } else {
                    $container[$model->findId()] = $model;
                }
                $container->index($feature, $model->findId());
            } else {
                $container[$model->findId()] = $model;
            }
        }
        return $model;
    }

    public static function clean(): void {
        foreach (Cabin\Container::getAllInstances() as $container) {
            $container->clear();
        }
    }

    public static function release(): void {
        foreach (Cabin\Container::getAllInstances() as $container) {
            $container->release();
        }
    }
}