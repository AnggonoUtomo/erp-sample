<?php

namespace App\Modules\HR\EmployeeDocuments\Transactions;

use Illuminate\Support\Facades\DB;

class EmployeeDocumentsTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
