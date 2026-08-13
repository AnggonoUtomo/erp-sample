<?php

namespace App\Modules\HR\WorkLocations\Infrastructure\Transactions;

use Illuminate\Support\Facades\DB;

class WorkLocationsTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
