<?php

namespace App\Modules\DocumentManagement\Foundation\Transactions;

use Illuminate\Support\Facades\DB;

class LogicalDocumentTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
