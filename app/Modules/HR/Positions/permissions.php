<?php

return [
    'permissions' => [
        'hr.view',
        'positions.view',
        'positions.create',
        'positions.update',
        'positions.delete',
        'positions.manage',
    ],
    'roles' => [
        'admin' => ['hr.view', 'positions.view', 'positions.create', 'positions.update', 'positions.delete', 'positions.manage'],
        'staff' => ['hr.view', 'positions.view'],
    ],
];
