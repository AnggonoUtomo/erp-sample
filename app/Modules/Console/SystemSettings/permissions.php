<?php

return [
    'permissions' => [
        'system-settings.view',
        'system-settings.update',
    ],
    'roles' => [
        'admin' => ['system-settings.view'],
        'staff' => [],
    ],
];
