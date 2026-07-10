<?php

use App\Modules\HR\WorkLocations\Providers\WorkLocationsServiceProvider;

return [
    'name' => 'WorkLocations',
    'project' => 'HR',
    'title' => 'Work Locations',
    'slug' => 'work-locations',
    'description' => 'Master lokasi kerja HR untuk employee profile, attendance, payroll, dan report organisasi.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        WorkLocationsServiceProvider::class,
    ],
    'dependencies' => [],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [
        'WorkLocationCreated',
        'WorkLocationUpdated',
        'WorkLocationDeleted',
        'WorkLocationRestored',
        'WorkLocationForceDeleted',
    ],
    'listeners' => [],
    'integrations' => [
        'upstream_for' => ['Employees', 'Attendance', 'Payroll'],
    ],
];
