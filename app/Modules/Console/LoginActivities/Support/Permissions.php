<?php

namespace App\Modules\Console\LoginActivities\Support;

class Permissions
{
    public static function permissions(): array
    {
        return [
            'login-activities.view',
        ];
    }

    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['login-activities.view'],
            'staff' => [],
        ];
    }
}
