<?php

namespace App\Modules\HR\Onboardings\Transactions;

use Illuminate\Support\Facades\DB;

class OnboardingTemplateTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
