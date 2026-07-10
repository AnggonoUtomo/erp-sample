<?php

namespace App\Modules\Console\AccessControls\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'roles.manage',
            'access-control.view',
            'access-control.create',
            'access-control.update',
            'access-control.delete',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['access-control.view'],
            'staff' => [],
        ];
    }
}
