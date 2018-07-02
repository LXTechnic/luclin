<?php

namespace Luclin\Foundation\Domains\Common;

use DB;

class TransactionProvider
{
    public function begin(): void {
        DB::beginTransaction();
    }

    public function commit(): void {
        DB::commit();
    }

    public function rollback(): void {
        DB::rollback();
    }

}