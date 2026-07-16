<?php

return [
    'permissions' => [
        'employee-movements.view',
        'employee-movements.create',
        'employee-movements.approve',
        'employee-movements.apply',
        'employee-movements.cancel',
        'employee-movements.archive',
        'employee-movements.restore',
        'employee-movements.manage',
    ],
    'roles' => [
        'admin' => ['hr.view', 'employee-movements.view', 'employee-movements.create', 'employee-movements.approve', 'employee-movements.apply', 'employee-movements.cancel', 'employee-movements.archive', 'employee-movements.restore', 'employee-movements.manage'],
        'hr-manager' => ['hr.view', 'employee-movements.view', 'employee-movements.create', 'employee-movements.approve', 'employee-movements.apply', 'employee-movements.cancel', 'employee-movements.archive', 'employee-movements.restore', 'employee-movements.manage'],
        'hr-officer' => ['hr.view', 'employee-movements.view', 'employee-movements.create'],
        'hr-viewer' => ['hr.view', 'employee-movements.view'],
    ],
];
