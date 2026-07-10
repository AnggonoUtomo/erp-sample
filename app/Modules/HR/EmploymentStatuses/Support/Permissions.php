<?php

namespace App\Modules\HR\EmploymentStatuses\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'hr.view',
            'employment-statuses.view',
            'employment-statuses.create',
            'employment-statuses.update',
            'employment-statuses.delete',
            'employment-statuses.restore',
            'employment-statuses.force-delete',
            'employment-statuses.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['hr.view', 'employment-statuses.view', 'employment-statuses.create', 'employment-statuses.update', 'employment-statuses.delete', 'employment-statuses.restore', 'employment-statuses.force-delete', 'employment-statuses.manage'],
            'hr-manager' => ['hr.view', 'employment-statuses.view', 'employment-statuses.create', 'employment-statuses.update', 'employment-statuses.delete', 'employment-statuses.restore', 'employment-statuses.force-delete', 'employment-statuses.manage'],
            'hr-officer' => ['hr.view', 'employment-statuses.view', 'employment-statuses.create', 'employment-statuses.update', 'employment-statuses.restore'],
            'hr-viewer' => ['hr.view', 'employment-statuses.view'],
            'staff' => ['hr.view', 'employment-statuses.view'],
        ];
    }
}
