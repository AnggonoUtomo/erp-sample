<?php

use App\Modules\HR\Offboardings\Providers\OffboardingsServiceProvider;

return [
    'name' => 'Offboardings',
    'project' => 'HR',
    'title' => 'Offboardings',
    'slug' => 'offboardings',
    'description' => 'Effective-dated employee exit workflow with stable checklist snapshots.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        OffboardingsServiceProvider::class,
    ],
    'dependencies' => [
        'Employees',
        'EmploymentStatuses',
        'Console.UserManagements',
    ],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [],
    'listeners' => [],
    'integrations' => [
        'optional_dependencies' => [
            'HR.EmployeeContracts',
        ],
    ],
];
