<?php

namespace App\Modules\HR\HRReferenceData\Transactions;

use Illuminate\Support\Facades\DB;

class HRReferenceDataTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
