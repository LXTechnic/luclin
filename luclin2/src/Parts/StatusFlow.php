<?php

namespace Luclin2\Parts;

use Illuminate\Database\Schema\Blueprint;
use DB;

/**
 * @property int $status
 */
trait StatusFlow
{
    protected $_statusChange = [];

    abstract protected function getStatusFlow(): array;

    protected static function migrateUpStatusFlow(Blueprint $table): void
    {
        $table->smallInteger('status')->default(static::getDefaultStatus());
    }

    protected static function migrateDownStatusFlow(Blueprint $table): void
    {
        $table->dropColumn('status');
    }

    public function isStatusChange($from, $to): bool {
        return $this->_statusChange == [$from, $to];
    }

    public function getStatusAttribute($value) {
        return $value === null ? static::getDefaultStatus() : $value;
    }

    public function setStatusAttribute($value) {
        if (!$this->_onFactory
            // && ($this->attributes['status'] ?? null) != $value
            && !$this->checkStatusFlow($value))
        {
            $this->raiseStatusFlowError($this->status, $value);
        }

        $from   = $this->attributes['status'] ?? static::getDefaultStatus();
        $to     = $value;
        $this->_statusChange = [$from, $to];
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
        if (!array_key_exists($nextStatus, $statusFlow)) {
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