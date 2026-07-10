<?php

return [
    'permissions' => [
        'hr.view',
        'employees.view',
        'employees.create',
        'employees.update',
        'employees.delete',
        'employees.restore',
        'employees.force-delete',
        'employees.manage',
    ],
    'roles' => [
        'admin' => ['hr.view', 'employees.view', 'employees.create', 'employees.update', 'employees.delete', 'employees.restore', 'employees.force-delete', 'employees.manage'],
        'hr-manager' => ['hr.view', 'employees.view', 'employees.create', 'employees.update', 'employees.delete', 'employees.restore', 'employees.force-delete', 'employees.manage'],
        'hr-officer' => ['hr.view', 'employees.view', 'employees.create', 'employees.update', 'employees.restore'],
        'hr-viewer' => ['hr.view', 'employees.view'],
        'staff' => ['hr.view', 'employees.view'],
    ],
];
