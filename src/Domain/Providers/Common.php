<?php

namespace Luclin\Domain\Providers;

use Luclin\Contracts;
use Luclin\Foundation;
use Luclin\Flow;

use Log;
use DB;

class Common extends \Luclin\Domain\Provider
{
    public static $type = self::TYPE_ACTION;

    public function doNothing(): void {}

    public function raise(\Throwable $e = null): void {
        throw $e ?: $this->_catch;
    }

    public function dd(...$arguments): void {
        dd(...$arguments);
    }

    public function dump(...$arguments): void {
        dump(...$arguments);
    }

    public function done($signal = 1) {
        return $signal;
    }

    public function transactionBegin(): void {
        DB::beginTransaction();
    }

    public function transactionCommit(): void {
        DB::commit();
    }

    public function transactionRollback(): void {
        DB::rollback();
    }

    public function log(string $message,
        $level = 'info', array $extra = []): void
    {
        Log::$level($message, $extra);
    }

    public function logCatch(string $message, $level = 'error'): void {
        $exc    = $this->_catch;
        $info   = $exc->getCode().
            ' - '.$exc->getMessage().
            ' - '.$exc->getFile().':'.$exc->getLine();
        $trace  = [];
        $count  = 0;
        foreach ($exc->getTrace() as $row) {
            $trace[] = ($row['file'] ?? $row['class']).
                ':'.($row['line'] ?? $row['function']);
            $count++;
            if ($count >= 20) {
                break;
            }
        }
        Log::$level("$message: $info", $trace);

    }

}
