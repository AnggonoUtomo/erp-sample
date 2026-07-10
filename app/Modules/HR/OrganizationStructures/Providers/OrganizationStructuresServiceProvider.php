<?php

namespace App\Modules\HR\OrganizationStructures\Providers;

use App\Modules\HR\OrganizationStructures\Models\OrganizationStructure;
use App\Modules\HR\OrganizationStructures\Policies\OrganizationStructurePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class OrganizationStructuresServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(OrganizationStructure::class, OrganizationStructurePolicy::class);
    }
}
