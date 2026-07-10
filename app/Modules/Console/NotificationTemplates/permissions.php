<?php

return [
    'permissions' => [
        'notification-templates.view',
        'notification-templates.update',
    ],
    'roles' => [
        'admin' => ['notification-templates.view', 'notification-templates.update'],
        'staff' => [],
    ],
];
