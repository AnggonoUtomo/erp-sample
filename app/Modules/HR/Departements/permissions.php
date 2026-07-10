<?php

return [
    'permissions' => [
        'hr.view',
        'departements.view',
        'departements.create',
        'departements.update',
        'departements.delete',
        'departements.manage',
    ],
    'roles' => [
        'admin' => ['hr.view', 'departements.view', 'departements.create', 'departements.update', 'departements.delete', 'departements.manage'],
        'staff' => ['hr.view', 'departements.view'],
    ],
];
