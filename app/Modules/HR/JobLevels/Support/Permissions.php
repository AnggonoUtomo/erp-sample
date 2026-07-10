<?php

namespace App\Modules\HR\JobLevels\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'hr.view',
            'job-levels.view',
            'job-levels.create',
            'job-levels.update',
            'job-levels.delete',
            'job-levels.restore',
            'job-levels.force-delete',
            'job-levels.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => self::permissions(),
            'staff' => ['hr.view', 'job-levels.view'],
            'hr-manager' => self::permissions(),
            'hr-officer' => ['hr.view', 'job-levels.view', 'job-levels.create', 'job-levels.update', 'job-levels.restore'],
            'hr-viewer' => ['hr.view', 'job-levels.view'],
        ];
    }
}
