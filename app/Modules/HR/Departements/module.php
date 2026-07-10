<?php

use App\Modules\HR\Departements\Providers\DepartementsServiceProvider;

return [
    'name' => 'Departements',
    'project' => 'HR',
    'title' => 'Departements',
    'slug' => 'departements',
    'description' => 'Master Departement dan struktur organisasi dasar HR.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        DepartementsServiceProvider::class,
    ],
    'dependencies' => [],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [],
    'listeners' => [],
    'integrations' => [],
];
