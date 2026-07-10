<?php

namespace App\Modules\Console\NotificationTemplates\Providers;

use App\Modules\Console\NotificationTemplates\Models\NotificationTemplate;
use App\Modules\Console\NotificationTemplates\Policies\NotificationTemplatePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class NotificationTemplatesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(NotificationTemplate::class, NotificationTemplatePolicy::class);
    }
}
