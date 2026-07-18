<?php

namespace App\Modules\HR\HRReports\Providers;

use App\Modules\HR\HRReports\Console\Commands\HRReportsContractsExpiringCommand;
use App\Modules\HR\HRReports\Console\Commands\HRReportsDocumentsExpiringCommand;
use App\Modules\HR\HRReports\Console\Commands\HRReportsSummaryCommand;
use Illuminate\Support\ServiceProvider;

class HRReportsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                HRReportsSummaryCommand::class,
                HRReportsContractsExpiringCommand::class,
                HRReportsDocumentsExpiringCommand::class,
            ]);
        }
    }
}
