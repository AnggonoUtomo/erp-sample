<?php

use App\Modules\HR\Employees\Providers\EmployeesServiceProvider;

return [
    'name' => 'Employees',
    'project' => 'HR',
    'title' => 'Employees',
    'slug' => 'employees',
    'description' => 'Master employee profile inti dengan avatar, data kerja, relasi user, dan referensi organisasi.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        EmployeesServiceProvider::class,
    ],
    'dependencies' => [
        'Departements',
        'Positions',
        'JobLevels',
        'WorkLocations',
        'EmploymentStatuses',
        'EmploymentTypes',
    ],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [
        'EmployeeCreated',
        'EmployeeUpdated',
        'EmployeeDeleted',
        'EmployeeRestored',
        'EmployeeForceDeleted',
    ],
    'listeners' => [],
    'integrations' => [
        'upstream_for' => ['Attendance', 'Payroll', 'DocumentManagement', 'CRM'],
        'depends_on' => ['Console.Users', 'HR.OrganizationFoundation'],
    ],
];
