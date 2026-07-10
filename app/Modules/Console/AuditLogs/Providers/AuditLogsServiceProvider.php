<?php

namespace App\Modules\Console\AuditLogs\Providers;

use App\Modules\Console\AuditLogs\Models\AuditLog;
use App\Modules\Console\AuditLogs\Policies\AuditLogPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuditLogsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(AuditLog::class, AuditLogPolicy::class);
    }
}
