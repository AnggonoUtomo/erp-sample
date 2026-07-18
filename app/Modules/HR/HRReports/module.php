<?php

use App\Modules\HR\HRReports\Providers\HRReportsServiceProvider;

return [
    'name' => 'HRReports',
    'project' => 'HR',
    'title' => 'HR Reports',
    'slug' => 'hr-reports',
    'description' => 'Read-only laporan HR untuk headcount, status kerja, contract expiry, dan document expiry.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        HRReportsServiceProvider::class,
    ],
    'commands' => [
        'hr:reports:summary',
        'hr:reports:contracts-expiring',
        'hr:reports:documents-expiring',
    ],
    'dependencies' => [
        'HR.Employees',
        'HR.EmployeeContracts',
        'HR.EmployeeDocuments',
        'HR.EmployeeMovements',
        'HR.Onboardings',
        'HR.Offboardings',
    ],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [],
    'listeners' => [],
    'integrations' => [
        'reads_from' => [
            'Employees',
            'EmployeeContracts',
            'EmployeeDocuments',
            'EmployeeMovements',
            'Onboardings',
            'Offboardings',
        ],
        'boundary' => 'read-only',
        'deferred' => ['exports', 'queued_reports', 'reporting_snapshots'],
    ],
];
