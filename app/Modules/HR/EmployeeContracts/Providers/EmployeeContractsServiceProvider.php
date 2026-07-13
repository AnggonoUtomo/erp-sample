<?php

namespace App\Modules\HR\EmployeeContracts\Providers;

use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeContracts\Policies\EmployeeContractPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class EmployeeContractsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(EmployeeContract::class, EmployeeContractPolicy::class);
    }
}
