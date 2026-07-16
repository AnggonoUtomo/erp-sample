<?php

namespace App\Modules\HR\EmployeeMovements\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'employee-movements.view',
            'employee-movements.create',
            'employee-movements.apply',
            'employee-movements.cancel',
            'employee-movements.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['employee-movements.view', 'employee-movements.create', 'employee-movements.apply', 'employee-movements.cancel', 'employee-movements.manage'],
            'staff' => [],
        ];
    }
}
