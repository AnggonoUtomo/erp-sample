<?php

return [
    'permissions' => [
        'hr.view',
        'hr-reference-data.view',
        'hr-reference-data.create',
        'hr-reference-data.update',
        'hr-reference-data.delete',
        'hr-reference-data.restore',
        'hr-reference-data.force-delete',
        'hr-reference-data.manage',
    ],
    'roles' => [
        'admin' => ['hr.view', 'hr-reference-data.view', 'hr-reference-data.create', 'hr-reference-data.update', 'hr-reference-data.delete', 'hr-reference-data.restore', 'hr-reference-data.force-delete', 'hr-reference-data.manage'],
        'hr-manager' => ['hr.view', 'hr-reference-data.view', 'hr-reference-data.create', 'hr-reference-data.update', 'hr-reference-data.delete', 'hr-reference-data.restore', 'hr-reference-data.force-delete', 'hr-reference-data.manage'],
        'hr-officer' => ['hr.view', 'hr-reference-data.view', 'hr-reference-data.create', 'hr-reference-data.update', 'hr-reference-data.restore'],
        'hr-viewer' => ['hr.view', 'hr-reference-data.view'],
        'staff' => ['hr.view', 'hr-reference-data.view'],
    ],
];
