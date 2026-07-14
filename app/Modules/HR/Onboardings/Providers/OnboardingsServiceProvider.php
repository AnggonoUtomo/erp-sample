<?php

namespace App\Modules\HR\Onboardings\Providers;

use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use App\Modules\HR\Onboardings\Policies\OnboardingTemplatePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class OnboardingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(OnboardingTemplate::class, OnboardingTemplatePolicy::class);
    }
}
