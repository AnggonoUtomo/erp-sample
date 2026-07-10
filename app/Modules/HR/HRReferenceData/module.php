<?php

use App\Modules\HR\HRReferenceData\Providers\HRReferenceDataServiceProvider;

return [
    'name' => 'HRReferenceData',
    'project' => 'HR',
    'title' => 'HR Reference Data',
    'slug' => 'hr-reference-data',
    'description' => 'Master referensi umum HR seperti gender, marital status, education level, religion, bank, dan blood type.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        HRReferenceDataServiceProvider::class,
    ],
    'dependencies' => [],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [
        'HRReferenceDataCreated',
        'HRReferenceDataUpdated',
        'HRReferenceDataDeleted',
        'HRReferenceDataRestored',
        'HRReferenceDataForceDeleted',
    ],
    'listeners' => [],
    'integrations' => [
        'upstream_for' => ['Employees', 'Attendance', 'Payroll', 'Document Management'],
    ],
];
