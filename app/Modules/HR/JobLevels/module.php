<?php

use App\Modules\HR\JobLevels\Providers\JobLevelsServiceProvider;

return [
    'name' => 'JobLevels',
    'project' => 'HR',
    'title' => 'Job Levels',
    'slug' => 'job-levels',
    'description' => 'Master level/grade jabatan HR untuk employee profile, approval, benefit, dan payroll.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        JobLevelsServiceProvider::class,
    ],
    'dependencies' => [],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [
        'JobLevelCreated',
        'JobLevelUpdated',
        'JobLevelDeleted',
        'JobLevelRestored',
        'JobLevelForceDeleted',
    ],
    'listeners' => [],
    'integrations' => [
        'upstream_for' => ['Employees', 'Attendance', 'Payroll'],
    ],
];
