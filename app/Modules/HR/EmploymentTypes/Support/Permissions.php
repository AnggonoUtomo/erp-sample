<?php

namespace App\Modules\HR\EmploymentTypes\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'hr.view',
            'employment-types.view',
            'employment-types.create',
            'employment-types.update',
            'employment-types.delete',
            'employment-types.restore',
            'employment-types.force-delete',
            'employment-types.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['hr.view', 'employment-types.view', 'employment-types.create', 'employment-types.update', 'employment-types.delete', 'employment-types.restore', 'employment-types.force-delete', 'employment-types.manage'],
            'hr-manager' => ['hr.view', 'employment-types.view', 'employment-types.create', 'employment-types.update', 'employment-types.delete', 'employment-types.restore', 'employment-types.force-delete', 'employment-types.manage'],
            'hr-officer' => ['hr.view', 'employment-types.view', 'employment-types.create', 'employment-types.update', 'employment-types.restore'],
            'hr-viewer' => ['hr.view', 'employment-types.view'],
            'staff' => ['hr.view', 'employment-types.view'],
        ];
    }
}
