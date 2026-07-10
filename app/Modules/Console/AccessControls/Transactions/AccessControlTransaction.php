<?php

namespace App\Modules\Console\AccessControls\Transactions;

use Closure;
use Illuminate\Support\Facades\DB;

class AccessControlTransaction
{
    /**
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public function run(Closure $callback): mixed
    {
        return DB::transaction($callback);
    }
}
