<?php

namespace App\Modules\HR\WorkLocations\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'hr.view',
            'work-locations.view',
            'work-locations.create',
            'work-locations.update',
            'work-locations.delete',
            'work-locations.restore',
            'work-locations.force-delete',
            'work-locations.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => self::permissions(),
            'staff' => ['hr.view', 'work-locations.view'],
            'hr-manager' => self::permissions(),
            'hr-officer' => ['hr.view', 'work-locations.view', 'work-locations.create', 'work-locations.update', 'work-locations.restore'],
            'hr-viewer' => ['hr.view', 'work-locations.view'],
        ];
    }
}
