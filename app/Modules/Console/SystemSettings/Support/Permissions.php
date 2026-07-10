<?php

namespace App\Modules\Console\SystemSettings\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'system-settings.view',
            'system-settings.update',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['system-settings.view'],
            'staff' => [],
        ];
    }
}
