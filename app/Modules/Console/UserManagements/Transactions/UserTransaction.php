<?php

namespace App\Modules\Console\UserManagements\Transactions;

use Closure;
use Illuminate\Support\Facades\DB;

class UserTransaction
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
