<?php

namespace App\Modules\HR\Departements\Providers;

use App\Modules\HR\Departements\Models\Departement;
use App\Modules\HR\Departements\Policies\DepartementPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class DepartementsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(Departement::class, DepartementPolicy::class);
    }
}
