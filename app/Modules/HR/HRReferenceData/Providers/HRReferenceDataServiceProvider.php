<?php

namespace App\Modules\HR\HRReferenceData\Providers;

use App\Modules\HR\HRReferenceData\Models\ReferenceData;
use App\Modules\HR\HRReferenceData\Policies\ReferenceDataPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class HRReferenceDataServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
    }

    public function boot(): void
    {
        Gate::policy(ReferenceData::class, ReferenceDataPolicy::class);
    }
}
