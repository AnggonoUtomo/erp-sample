<?php

namespace App\Modules\HR\EmploymentStatuses\Providers;

use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\EmploymentStatuses\Policies\EmploymentStatusPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class EmploymentStatusesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(EmploymentStatus::class, EmploymentStatusPolicy::class);
    }
}
