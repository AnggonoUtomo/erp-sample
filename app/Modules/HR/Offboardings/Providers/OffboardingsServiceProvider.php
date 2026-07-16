<?php

namespace App\Modules\HR\Offboardings\Providers;

use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use App\Modules\HR\Offboardings\Policies\OffboardingTemplatePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class OffboardingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(OffboardingTemplate::class, OffboardingTemplatePolicy::class);
    }
}
