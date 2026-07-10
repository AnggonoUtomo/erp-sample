<?php

namespace App\Modules\Console\ActivityCenters\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'activity-center.view',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['activity-center.view'],
            'staff' => [],
        ];
    }
}
