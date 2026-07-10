<?php

namespace App\Modules\HR\EmploymentTypes\Providers;

use App\Modules\HR\EmploymentTypes\Models\EmploymentType;
use App\Modules\HR\EmploymentTypes\Policies\EmploymentTypePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class EmploymentTypesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(EmploymentType::class, EmploymentTypePolicy::class);
    }
}
