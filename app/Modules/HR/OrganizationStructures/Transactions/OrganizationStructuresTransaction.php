<?php

namespace App\Modules\HR\OrganizationStructures\Transactions;

use Illuminate\Support\Facades\DB;

class OrganizationStructuresTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
