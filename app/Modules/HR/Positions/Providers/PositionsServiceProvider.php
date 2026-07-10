<?php

namespace App\Modules\HR\Positions\Providers;

use App\Modules\HR\Positions\Models\Position;
use App\Modules\HR\Positions\Policies\PositionPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class PositionsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(Position::class, PositionPolicy::class);
    }
}
