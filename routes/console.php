<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(fn () => app(\App\Modules\Console\SchedulerMonitors\Services\SchedulerMonitorService::class)->recordHeartbeat())
    ->name('scheduler-monitor:heartbeat')
    ->everyMinute()
    ->withoutOverlapping();
