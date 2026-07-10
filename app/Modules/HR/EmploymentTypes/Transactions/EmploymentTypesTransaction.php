<?php

namespace App\Modules\HR\EmploymentTypes\Transactions;

use Illuminate\Support\Facades\DB;

class EmploymentTypesTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
