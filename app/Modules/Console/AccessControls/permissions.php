<?php

return [
    'permissions' => [
        'roles.manage',
        'access-control.view',
        'access-control.create',
        'access-control.update',
        'access-control.delete',
    ],
    'roles' => [
        'admin' => ['access-control.view'],
        'staff' => [],
    ],
];
