<?php

namespace App\Modules\HR\Employees\Providers;

use App\Modules\HR\Employees\Integration\Adapters\EloquentEmployeeTerminationAdapter;
use App\Modules\HR\Employees\Integration\Contracts\EmployeeTerminationGateway;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Employees\Policies\EmployeePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class EmployeesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        $this->app->bind(EmployeeTerminationGateway::class, EloquentEmployeeTerminationAdapter::class);
    }

    public function boot(): void
    {
        Gate::policy(Employee::class, EmployeePolicy::class);
    }
}
