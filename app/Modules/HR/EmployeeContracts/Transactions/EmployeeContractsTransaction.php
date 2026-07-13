<?php

namespace App\Modules\HR\EmployeeContracts\Transactions;

use Closure;
use Illuminate\Support\Facades\DB;

class EmployeeContractsTransaction
{
    public function run(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
