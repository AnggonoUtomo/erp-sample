<?php

use App\Modules\HR\EmployeeMovements\Integration\Events\EmployeeMovementAppliedV1;
use App\Modules\HR\EmployeeMovements\Providers\EmployeeMovementsServiceProvider;

return [
    'name' => 'EmployeeMovements',
    'project' => 'HR',
    'title' => 'Employee Movements',
    'slug' => 'employee-movements',
    'description' => 'Effective-dated employee assignment changes with before/after history.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        EmployeeMovementsServiceProvider::class,
    ],
    'dependencies' => ['Employees', 'Departements', 'Positions', 'WorkLocations', 'EmploymentStatuses', 'EmploymentTypes', 'EmployeeContracts'],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'commands' => ['hr:employee-movements:apply-due'],
    'events' => ['EmployeeMovementAppliedV1'],
    'listeners' => [],
    'integrations' => [
        'contracts' => [[
            'name' => 'EmployeeMovementApplied',
            'schema_version' => 1,
            'event' => EmployeeMovementAppliedV1::class,
            'schema' => 'Integration/Schemas/employee-movement-applied-v1.json',
        ]],
    ],
];
