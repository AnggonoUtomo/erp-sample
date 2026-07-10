<?php

namespace App\Modules\Console\SystemSettings\Providers;

use App\Modules\Console\SystemSettings\Policies\SystemSettingPolicy;
use App\Modules\Console\SystemSettings\Services\SystemSettingService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class SystemSettingsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(SystemSettingService::class, SystemSettingPolicy::class);
    }
}
