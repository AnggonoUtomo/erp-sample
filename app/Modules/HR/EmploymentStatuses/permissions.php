<?php

return [
    'permissions' => [
        'hr.view',
        'employment-statuses.view',
        'employment-statuses.create',
        'employment-statuses.update',
        'employment-statuses.delete',
        'employment-statuses.restore',
        'employment-statuses.force-delete',
        'employment-statuses.manage',
    ],
    'roles' => [
        'admin' => ['hr.view', 'employment-statuses.view', 'employment-statuses.create', 'employment-statuses.update', 'employment-statuses.delete', 'employment-statuses.restore', 'employment-statuses.force-delete', 'employment-statuses.manage'],
        'hr-manager' => ['hr.view', 'employment-statuses.view', 'employment-statuses.create', 'employment-statuses.update', 'employment-statuses.delete', 'employment-statuses.restore', 'employment-statuses.force-delete', 'employment-statuses.manage'],
        'hr-officer' => ['hr.view', 'employment-statuses.view', 'employment-statuses.create', 'employment-statuses.update', 'employment-statuses.restore'],
        'hr-viewer' => ['hr.view', 'employment-statuses.view'],
        'staff' => ['hr.view', 'employment-statuses.view'],
    ],
];
