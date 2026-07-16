<?php

namespace App\Modules\HR\Offboardings\Transactions;

use Illuminate\Support\Facades\DB;

class OffboardingTemplateTransaction
{
    public function run(callable $callback): mixed
    {
        return DB::transaction($callback);
    }
}
