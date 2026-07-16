<?php

namespace App\Modules\HR\Offboardings\Providers;

use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTask;
use App\Modules\HR\Offboardings\Models\OffboardingTemplate;
use App\Modules\HR\Offboardings\Policies\OffboardingPolicy;
use App\Modules\HR\Offboardings\Policies\OffboardingTaskPolicy;
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
        Gate::policy(Offboarding::class, OffboardingPolicy::class);
        Gate::policy(OffboardingTask::class, OffboardingTaskPolicy::class);
        Gate::policy(OffboardingTemplate::class, OffboardingTemplatePolicy::class);
    }
}
