<?php

namespace App\Modules\HR\EmploymentStatuses\Transactions;

use Illuminate\Support\Facades\DB;

class EmploymentStatusesTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
