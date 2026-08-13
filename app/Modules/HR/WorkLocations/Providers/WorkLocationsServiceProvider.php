<?php

namespace App\Modules\HR\WorkLocations\Providers;

use App\Modules\HR\WorkLocations\Infrastructure\Models\WorkLocation;
use App\Modules\HR\WorkLocations\Presentation\Policies\WorkLocationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class WorkLocationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(WorkLocation::class, WorkLocationPolicy::class);
    }
}
