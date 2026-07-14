<?php

namespace App\Modules\HR\Onboardings\Providers;

use App\Modules\HR\Onboardings\Console\Commands\OnboardingsOverdueCommand;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTask;
use App\Modules\HR\Onboardings\Models\OnboardingTemplate;
use App\Modules\HR\Onboardings\Policies\OnboardingPolicy;
use App\Modules\HR\Onboardings\Policies\OnboardingTaskPolicy;
use App\Modules\HR\Onboardings\Policies\OnboardingTemplatePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class OnboardingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([OnboardingsOverdueCommand::class]);
        }
    }

    public function boot(): void
    {
        Gate::policy(Onboarding::class, OnboardingPolicy::class);
        Gate::policy(OnboardingTemplate::class, OnboardingTemplatePolicy::class);
        Gate::policy(OnboardingTask::class, OnboardingTaskPolicy::class);
    }
}
