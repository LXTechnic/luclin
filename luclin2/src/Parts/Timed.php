<?php

namespace Luclin2\Parts;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property string $active_time
 * @property string $expire_time
 */
trait Timed
{
    protected static function migrateUpTimed(Blueprint $table): void
    {
        $table->dateTime('active_time')->nullable();
        $table->dateTime('expire_time')->nullable();
    }

    protected static function migrateDownTimed(Blueprint $table): void
    {
        $table->dropColumn('active_time');
        $table->dropColumn('expire_time');
    }

    public function isExpired(int $before = 0): bool {
        return $this->expire_time && \luc\time::now()
            ->addSeconds($before)
            ->gt($this->expire_time);
    }

    public function isActived(int $offset = 0): bool {
        $activeTime = $this->active_time;
        if ($offset) {
            $activeTime = date('Y-m-d H:i:s', strtotime($activeTime) + $offset);
        }
        if ($activeTime) {
            if (\luc\time::now()->gte($activeTime)) {
                if ($this->isExpired()) {
                    return false;
                }
                return true;
            }
            return false;
        }
        return true;
    }

    public function queryActived(Builder $query = null): Builder {
        !$query && $query = static::query();
        $now = \luc\time::now();
        $query->where('active_time', '<=', $now);
        if (method_exists($this, 'isExpired')) {
            $query->where('expire_time', '>', $now);
        }
        return $query;
    }
}