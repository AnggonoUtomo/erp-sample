<?php

namespace App\Modules\DocumentManagement\Foundation\Ingestion\Transactions;

use Illuminate\Support\Facades\DB;

class DocumentIngestionTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
