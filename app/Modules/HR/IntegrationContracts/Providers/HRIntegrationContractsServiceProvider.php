<?php

namespace App\Modules\HR\IntegrationContracts\Providers;

use App\Modules\HR\IntegrationContracts\Contracts\EmployeeAssignmentSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeContractSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeDocumentComplianceSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Services\EloquentEmployeeAssignmentSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Services\EloquentEmployeeContractSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Services\EloquentEmployeeDocumentComplianceSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Services\EloquentEmployeeSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Support\HRIntegrationContractRegistry;
use App\Modules\HR\IntegrationContracts\Support\HRIntegrationEventRegistry;
use Illuminate\Support\ServiceProvider;

class HRIntegrationContractsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HRIntegrationEventRegistry::class);
        $this->app->singleton(HRIntegrationContractRegistry::class);
        $this->app->bind(EmployeeAssignmentSnapshotProvider::class, EloquentEmployeeAssignmentSnapshotProvider::class);
        $this->app->bind(EmployeeContractSnapshotProvider::class, EloquentEmployeeContractSnapshotProvider::class);
        $this->app->bind(EmployeeDocumentComplianceSnapshotProvider::class, EloquentEmployeeDocumentComplianceSnapshotProvider::class);
        $this->app->bind(EmployeeSnapshotProvider::class, EloquentEmployeeSnapshotProvider::class);
    }
}
