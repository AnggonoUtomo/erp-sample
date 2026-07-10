<?php

namespace App\Modules\HR\Employees\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'hr.view',
            'employees.view',
            'employees.create',
            'employees.update',
            'employees.delete',
            'employees.restore',
            'employees.force-delete',
            'employees.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['hr.view', 'employees.view', 'employees.create', 'employees.update', 'employees.delete', 'employees.restore', 'employees.force-delete', 'employees.manage'],
            'hr-manager' => ['hr.view', 'employees.view', 'employees.create', 'employees.update', 'employees.delete', 'employees.restore', 'employees.force-delete', 'employees.manage'],
            'hr-officer' => ['hr.view', 'employees.view', 'employees.create', 'employees.update', 'employees.restore'],
            'hr-viewer' => ['hr.view', 'employees.view'],
            'staff' => ['hr.view', 'employees.view'],
        ];
    }
}
