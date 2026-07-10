<?php

namespace App\Modules\HR\OrganizationStructures\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'hr.view',
            'organization-structures.view',
            'organization-structures.create',
            'organization-structures.update',
            'organization-structures.delete',
            'organization-structures.restore',
            'organization-structures.force-delete',
            'organization-structures.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['hr.view', 'organization-structures.view', 'organization-structures.create', 'organization-structures.update', 'organization-structures.delete', 'organization-structures.restore', 'organization-structures.force-delete', 'organization-structures.manage'],
            'hr-manager' => ['hr.view', 'organization-structures.view', 'organization-structures.create', 'organization-structures.update', 'organization-structures.delete', 'organization-structures.restore', 'organization-structures.force-delete', 'organization-structures.manage'],
            'hr-officer' => ['hr.view', 'organization-structures.view', 'organization-structures.create', 'organization-structures.update', 'organization-structures.restore'],
            'hr-viewer' => ['hr.view', 'organization-structures.view'],
            'staff' => ['hr.view', 'organization-structures.view'],
        ];
    }
}
