<?php

namespace App\Modules\Console\UserManagements\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'users.restore',
            'users.force-delete',
            'users.impersonate',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['users.view', 'users.create', 'users.update'],
            'staff' => ['users.view'],
        ];
    }
}
