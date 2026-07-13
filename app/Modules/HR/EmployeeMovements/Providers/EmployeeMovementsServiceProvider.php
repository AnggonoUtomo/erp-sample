<?php

namespace App\Modules\HR\EmployeeMovements\Providers;

use App\Modules\HR\EmployeeMovements\Models\EmployeeMovement;
use App\Modules\HR\EmployeeMovements\Policies\EmployeeMovementPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class EmployeeMovementsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(EmployeeMovement::class, EmployeeMovementPolicy::class);
    }
}
