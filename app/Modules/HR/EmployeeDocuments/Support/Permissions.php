<?php

namespace App\Modules\HR\EmployeeDocuments\Support;

class Permissions
{
    /**
     * @return array<int, string>
     */
    public static function permissions(): array
    {
        return [
            'employee-documents.view',
            'employee-documents.create',
            'employee-documents.update',
            'employee-documents.verify',
            'employee-documents.archive',
            'employee-documents.restore',
            'employee-documents.attach',
            'employee-documents.manage',
        ];
    }

    /**
     * @return array<string, array<int, string>>
     */
    public static function defaultRolePermissions(): array
    {
        return [
            'admin' => ['hr.view', 'employee-documents.view', 'employee-documents.create', 'employee-documents.update', 'employee-documents.verify', 'employee-documents.archive', 'employee-documents.restore', 'employee-documents.attach', 'employee-documents.manage'],
            'hr-manager' => ['hr.view', 'employee-documents.view', 'employee-documents.create', 'employee-documents.update', 'employee-documents.verify', 'employee-documents.archive', 'employee-documents.restore', 'employee-documents.attach', 'employee-documents.manage'],
            'hr-officer' => ['hr.view', 'employee-documents.view', 'employee-documents.create', 'employee-documents.update'],
            'hr-viewer' => ['hr.view', 'employee-documents.view'],
        ];
    }
}
