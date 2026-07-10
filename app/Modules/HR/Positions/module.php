<?php

use App\Modules\HR\Positions\Providers\PositionsServiceProvider;

return [
    'name' => 'Positions',
    'project' => 'HR',
    'title' => 'Positions',
    'slug' => 'positions',
    'description' => 'Master jabatan dan job title HR yang terhubung ke Departement.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        PositionsServiceProvider::class,
    ],
    'dependencies' => [
        'HR/Departements',
    ],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [
        'PositionCreated',
        'PositionUpdated',
        'PositionDeleted',
    ],
    'listeners' => [],
    'integrations' => [
        'upstream_for' => ['Employees', 'Attendance', 'Payroll'],
    ],
];
