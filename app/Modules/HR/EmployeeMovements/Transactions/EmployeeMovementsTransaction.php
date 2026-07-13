<?php

namespace App\Modules\HR\EmployeeMovements\Transactions;

use Illuminate\Support\Facades\DB;

class EmployeeMovementsTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
