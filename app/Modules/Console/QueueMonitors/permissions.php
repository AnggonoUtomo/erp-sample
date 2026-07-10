<?php

return [
    'permissions' => [
        'queue-monitor.view',
        'queue-monitor.manage',
    ],
    'roles' => [
        'admin' => ['queue-monitor.view', 'queue-monitor.manage'],
        'staff' => [],
    ],
];
