<?php

namespace App\Modules\Console\SchedulerMonitors\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'scheduler-monitor.view',
            'scheduler-monitor.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['scheduler-monitor.view', 'scheduler-monitor.manage'],
            'staff' => [],
        ];
    }
}
