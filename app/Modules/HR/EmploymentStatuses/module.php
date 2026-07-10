<?php

use App\Modules\HR\EmploymentStatuses\Providers\EmploymentStatusesServiceProvider;

return [
    'name' => 'EmploymentStatuses',
    'project' => 'HR',
    'title' => 'Employment Statuses',
    'slug' => 'employment-statuses',
    'description' => 'Master status kerja untuk employee lifecycle, attendance eligibility, dan payroll inclusion.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        EmploymentStatusesServiceProvider::class,
    ],
    'dependencies' => [],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [
        'EmploymentStatusCreated',
        'EmploymentStatusUpdated',
        'EmploymentStatusDeleted',
        'EmploymentStatusRestored',
        'EmploymentStatusForceDeleted',
    ],
    'listeners' => [],
    'integrations' => [
        'upstream_for' => ['Employees', 'Attendance', 'Payroll'],
    ],
];
