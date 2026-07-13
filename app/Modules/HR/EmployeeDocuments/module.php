<?php

use App\Modules\HR\EmployeeDocuments\Providers\EmployeeDocumentsServiceProvider;

return [
    'name' => 'EmployeeDocuments',
    'project' => 'HR',
    'title' => 'Employee Documents',
    'slug' => 'employee-documents',
    'description' => 'HR-owned employee document metadata, expiry, and verification.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        EmployeeDocumentsServiceProvider::class,
    ],
    'dependencies' => ['Employees', 'HRReferenceData'],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'commands' => ['hr:documents-expiring'],
    'events' => [],
    'listeners' => [],
    'integrations' => [],
];
