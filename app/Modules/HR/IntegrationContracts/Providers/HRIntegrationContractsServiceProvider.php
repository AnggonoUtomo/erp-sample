<?php

namespace App\Modules\HR\IntegrationContracts\Providers;

use App\Modules\HR\IntegrationContracts\Support\HRIntegrationContractRegistry;
use Illuminate\Support\ServiceProvider;

class HRIntegrationContractsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(HRIntegrationContractRegistry::class);
    }
}
