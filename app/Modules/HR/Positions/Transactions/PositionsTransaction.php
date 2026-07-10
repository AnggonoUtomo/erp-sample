<?php

namespace App\Modules\HR\Positions\Transactions;

use Illuminate\Support\Facades\DB;

class PositionsTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
