<?php

use App\Modules\HR\EmploymentTypes\Providers\EmploymentTypesServiceProvider;

return [
    'name' => 'EmploymentTypes',
    'project' => 'HR',
    'title' => 'Employment Types',
    'slug' => 'employment-types',
    'description' => 'Master tipe hubungan kerja untuk employee contract, benefit, overtime, dan payroll rules.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        EmploymentTypesServiceProvider::class,
    ],
    'dependencies' => [],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [
        'EmploymentTypeCreated',
        'EmploymentTypeUpdated',
        'EmploymentTypeDeleted',
        'EmploymentTypeRestored',
        'EmploymentTypeForceDeleted',
    ],
    'listeners' => [],
    'integrations' => [
        'upstream_for' => ['Employees', 'Attendance', 'Payroll'],
    ],
];
