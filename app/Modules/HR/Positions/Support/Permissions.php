<?php

namespace App\Modules\HR\Positions\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'hr.view',
            'positions.view',
            'positions.create',
            'positions.update',
            'positions.delete',
            'positions.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['hr.view', 'positions.view', 'positions.create', 'positions.update', 'positions.delete', 'positions.manage'],
            'staff' => ['hr.view', 'positions.view'],
            'hr-manager' => ['hr.view', 'positions.view', 'positions.create', 'positions.update', 'positions.delete', 'positions.manage'],
            'hr-officer' => ['hr.view', 'positions.view', 'positions.create', 'positions.update'],
            'hr-viewer' => ['hr.view', 'positions.view'],
        ];
    }
}
