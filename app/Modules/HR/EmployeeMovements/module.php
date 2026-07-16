<?php

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
    'events' => ['EmployeeMovementApplied'],
    'listeners' => [],
    'integrations' => [],
];
