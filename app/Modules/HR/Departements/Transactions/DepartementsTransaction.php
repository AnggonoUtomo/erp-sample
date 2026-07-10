<?php

namespace App\Modules\HR\Departements\Transactions;

use Illuminate\Support\Facades\DB;

class DepartementsTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
