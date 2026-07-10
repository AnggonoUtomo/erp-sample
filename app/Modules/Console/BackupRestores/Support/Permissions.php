<?php

namespace App\Modules\Console\BackupRestores\Support;

class Permissions
{
    public static function permissions(): array
    {
        return [
            'backup-restore.view',
            'backup-restore.export',
            'backup-restore.restore',
            'backup-restore.full-export',
            'backup-restore.full-restore',
        ];
    }

    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['backup-restore.view', 'backup-restore.export'],
            'staff' => [],
        ];
    }
}
