<?php

namespace App\Modules\HR\Employees\Transactions;

use Illuminate\Support\Facades\DB;

class EmployeesTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
