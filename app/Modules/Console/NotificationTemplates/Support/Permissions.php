<?php

namespace App\Modules\Console\NotificationTemplates\Support;

class Permissions
{
    public static function permissions(): array
    {
        return [
            'notification-templates.view',
            'notification-templates.update',
        ];
    }

    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['notification-templates.view', 'notification-templates.update'],
            'staff' => [],
        ];
    }
}
