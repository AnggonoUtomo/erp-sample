<?php

namespace App\Modules\Console\BackupRestores\Providers;

use App\Modules\Console\BackupRestores\Policies\BackupRestorePolicy;
use App\Modules\Console\BackupRestores\Services\BackupRestoreService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class BackupRestoresServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(BackupRestoreService::class, BackupRestorePolicy::class);
    }
}
