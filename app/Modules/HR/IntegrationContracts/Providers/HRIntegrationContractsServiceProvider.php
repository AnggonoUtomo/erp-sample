<?php

namespace App\Modules\HR\IntegrationContracts\Providers;

use App\Modules\HR\IntegrationContracts\Contracts\EmployeeSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Services\EloquentEmployeeSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Support\HRIntegrationContractRegistry;
use Illuminate\Support\ServiceProvider;

class HRIntegrationContractsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HRIntegrationContractRegistry::class);
        $this->app->bind(EmployeeSnapshotProvider::class, EloquentEmployeeSnapshotProvider::class);
    }
}
