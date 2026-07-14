<?php

namespace App\Modules\HR\Onboardings\Transactions;

use Illuminate\Support\Facades\DB;

class OnboardingTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
