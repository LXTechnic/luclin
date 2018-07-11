<?php

namespace Luclin\Cabin\Parts;

use Illuminate\Database\Schema\Blueprint;
use DB;

/**
 * @property int $reply_to
 * @property int $replys_count
 */
trait StatusFlow
{
    abstract protected function getStatusFlow(): array;

    protected static function migrateUpStatusFlow(Blueprint $table): void
    {
        $table->smallInteger('status')->default(static::getDefaultStatus());
    }

    protected static function migrateDownStatusFlow(Blueprint $table): void
    {
        $table->dropColumn('status');
    }


    public function setStatusAttribute($value) {
        if (!$this->checkStatusFlow($value)) {
            $this->raiseStatusFlowError($this->status, $value);
        }
        $this->attributes['status'] = $value;
    }

    /**
     * 更新状态时检查状态流是否合法
     *
     * @param int $nextStatus
     * @return boolean
     * @throws \UnexpectedValueException
     */
    protected function checkStatusFlow($nextStatus) {
        $statusFlow = $this->getStatusFlow();
        if (!isset($statusFlow[$nextStatus])) {
            $this->raiseStatusFlowNotExist($nextStatus);
        }

        if (is_array($statusFlow[$nextStatus])) {
            return in_array($this->status, $statusFlow[$nextStatus]);
        }

        if (is_bool($statusFlow[$nextStatus])) {
            if ($statusFlow[$nextStatus]) {
                return true;
            }
            if ($this->status === null) {
                return true;
            } else {
                return false;
            }
        }

        return $this->status == $statusFlow[$nextStatus];
    }

    protected function raiseStatusFlowNotExist($nextStatus) {
        throw new \UnexpectedValueException("next status $nextStatus not exist");
    }

    protected static function getDefaultStatus(): int {
        return 0;
    }

    protected function raiseStatusFlowError($currentStatus, $nextStatus) {
        throw \luc\raise('luclin.status_flow_error');
    }

}