<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;

/**
 * duration默认不变更start time和end time
 *
 * @property string $start_time
 * @property string $end_time
 * @property string $duration
 */
trait HasDuration
{
    protected static function migrateUpHasDuration(Blueprint $table): void
    {
        $table->dateTime('start_time')->nullable();
        $table->dateTime('end_time')->nullable();
        $table->integer('duration')->nullable();
    }

    protected static function migrateDownHasDuration(Blueprint $table): void
    {
        $table->dropColumn('start_time');
        $table->dropColumn('end_time');
        $table->dropColumn('duration');
    }

    public function setStartTimeAttribute($value) {
        $this->attributes['start_time'] = $value;
        if ($this->end_time) {
            $this->updateDurationByStartEndTime();
        }
    }

    public function setEndTimeAttribute($value) {
        $this->attributes['end_time'] = $value;
        if ($this->start_time) {
            $this->updateDurationByStartEndTime();
        }
    }

    public function updateDurationByStartEndTime() {
        $this->attributes['duration'] = strtotime($this->end_time) - strtotime($this->start_time);
    }

}