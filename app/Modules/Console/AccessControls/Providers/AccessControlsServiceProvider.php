<?php

namespace App\Modules\Console\AccessControls\Providers;

use App\Modules\Console\AccessControls\Policies\AccessControlPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

class AccessControlsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(Role::class, AccessControlPolicy::class);
        Gate::define('access-control.manage', [AccessControlPolicy::class, 'manage']);
    }
}
