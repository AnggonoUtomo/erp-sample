<?php

use App\Modules\HR\OrganizationStructures\Providers\OrganizationStructuresServiceProvider;

return [
    'name' => 'OrganizationStructures',
    'project' => 'HR',
    'title' => 'Organization Structures',
    'slug' => 'organization-structures',
    'description' => 'Struktur organisasi formal untuk hierarchy departement, jabatan, reporting line, dan fondasi approval lintas project.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        OrganizationStructuresServiceProvider::class,
    ],
    'dependencies' => [],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [
        'OrganizationStructureCreated',
        'OrganizationStructureUpdated',
        'OrganizationStructureDeleted',
        'OrganizationStructureRestored',
        'OrganizationStructureForceDeleted',
    ],
    'listeners' => [],
    'integrations' => [
        'upstream_for' => ['Employees', 'Attendance', 'Payroll'],
    ],
];
