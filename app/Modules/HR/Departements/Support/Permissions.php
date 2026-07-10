<?php

namespace App\Modules\HR\Departements\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'hr.view',
            'departements.view',
            'departements.create',
            'departements.update',
            'departements.delete',
            'departements.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['hr.view', 'departements.view', 'departements.create', 'departements.update', 'departements.delete', 'departements.manage'],
            'staff' => ['hr.view', 'departements.view'],
            'hr-manager' => ['hr.view', 'departements.view', 'departements.create', 'departements.update', 'departements.delete', 'departements.manage'],
            'hr-officer' => ['hr.view', 'departements.view', 'departements.create', 'departements.update'],
            'hr-viewer' => ['hr.view', 'departements.view'],
        ];
    }
}
