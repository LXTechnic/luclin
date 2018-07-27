<?php

namespace Luclin\Foundation\Domains;

use Luclin\Flow\Domain;

class Common extends Domain
{

    public function id(): string {
        return 'common';
    }

    public function doNothing(): void {}

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
        array $extra = [], $level = 'info'): void
    {
        Log::$level($message, $extra);
    }

}