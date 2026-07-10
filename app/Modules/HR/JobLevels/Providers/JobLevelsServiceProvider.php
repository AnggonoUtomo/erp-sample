<?php

namespace App\Modules\HR\JobLevels\Providers;

use App\Modules\HR\JobLevels\Models\JobLevel;
use App\Modules\HR\JobLevels\Policies\JobLevelPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class JobLevelsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(JobLevel::class, JobLevelPolicy::class);
    }
}
