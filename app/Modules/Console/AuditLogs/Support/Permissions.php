<?php

namespace App\Modules\Console\AuditLogs\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'audit-logs.view',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['audit-logs.view'],
            'staff' => [],
        ];
    }
}
