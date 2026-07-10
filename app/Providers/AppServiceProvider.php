<?php

namespace App\Providers;

use App\Models\User;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($ability === 'impersonate') {
                return null;
            }

            return $user->hasRole('super-admin') ? true : null;
        });

        app(SystemSettingService::class)->applyMailSettings();
        app(SystemSettingService::class)->applyLocalizationSettings();
        app(SystemSettingService::class)->applySecurityPolicy();
    }
}
