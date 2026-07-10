<?php

namespace App\Modules\Console\QueueMonitors\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'queue-monitor.view',
            'queue-monitor.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['queue-monitor.view', 'queue-monitor.manage'],
            'staff' => [],
        ];
    }
}
