<?php

return [
    'permissions' => [
        'users.view',
        'users.create',
        'users.update',
        'users.delete',
        'users.restore',
        'users.force-delete',
        'users.impersonate',
    ],
    'roles' => [
        'admin' => ['users.view', 'users.create', 'users.update'],
        'staff' => ['users.view'],
    ],
];
