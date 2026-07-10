<?php

namespace App\Modules\Console\UserManagements\Providers;

use App\Models\User;
use App\Modules\Console\UserManagements\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class UserManagementsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
    }
}
