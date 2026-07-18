<?php

return [
    'permissions' => [
        'hr.view',
        'hr-reports.view',
        'hr-reports.export',
        'hr-reports.manage',
    ],
    'roles' => [
        'admin' => ['hr.view', 'hr-reports.view', 'hr-reports.export', 'hr-reports.manage'],
        'hr-manager' => ['hr.view', 'hr-reports.view', 'hr-reports.export', 'hr-reports.manage'],
        'hr-officer' => ['hr.view', 'hr-reports.view'],
        'hr-viewer' => ['hr.view', 'hr-reports.view'],
        'staff' => [],
    ],
];
