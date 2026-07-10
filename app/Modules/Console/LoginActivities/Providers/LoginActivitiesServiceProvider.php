<?php

namespace App\Modules\Console\LoginActivities\Providers;

use App\Modules\Console\LoginActivities\Models\LoginActivity;
use App\Modules\Console\LoginActivities\Policies\LoginActivityPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class LoginActivitiesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(LoginActivity::class, LoginActivityPolicy::class);
    }
}
