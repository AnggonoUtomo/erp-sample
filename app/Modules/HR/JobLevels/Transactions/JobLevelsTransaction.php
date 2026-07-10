<?php

namespace App\Modules\HR\JobLevels\Transactions;

use Illuminate\Support\Facades\DB;

class JobLevelsTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
