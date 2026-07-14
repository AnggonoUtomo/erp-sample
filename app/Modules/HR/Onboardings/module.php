<?php

use App\Modules\HR\Onboardings\Providers\OnboardingsServiceProvider;

return [
    'name' => 'Onboardings',
    'project' => 'HR',
    'title' => 'Onboardings',
    'slug' => 'onboardings',
    'description' => 'Checklist-driven employee onboarding with stable task snapshots.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        OnboardingsServiceProvider::class,
    ],
    'dependencies' => [
        'Employees',
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
