<?php

namespace App\Modules\HR\EmployeeContracts\Providers;

use App\Modules\HR\EmployeeContracts\Console\Commands\ContractsExpiringCommand;
use App\Modules\HR\EmployeeContracts\Integration\Adapters\EloquentEmployeeContractEmploymentTypeGuard;
use App\Modules\HR\EmployeeContracts\Integration\Adapters\EloquentEmployeeContractTerminationAdapter;
use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractEmploymentTypeGuard;
use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractSnapshotReader;
use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractTerminationGateway;
use App\Modules\HR\EmployeeContracts\Integration\Projectors\EmployeeContractSnapshotProjector;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeContracts\Policies\EmployeeContractPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class EmployeeContractsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(EmployeeContractSnapshotReader::class, EmployeeContractSnapshotProjector::class);
        $this->app->bind(EmployeeContractEmploymentTypeGuard::class, EloquentEmployeeContractEmploymentTypeGuard::class);
        $this->app->bind(EmployeeContractTerminationGateway::class, EloquentEmployeeContractTerminationAdapter::class);
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        Gate::policy(EmployeeContract::class, EmployeeContractPolicy::class);

        if ($this->app->runningInConsole()) {
            $this->commands([ContractsExpiringCommand::class]);
        }
    }
}
