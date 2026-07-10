<?php

return [
    'permissions' => [
        'scheduler-monitor.view',
        'scheduler-monitor.manage',
    ],
    'roles' => [
        'admin' => ['scheduler-monitor.view', 'scheduler-monitor.manage'],
        'staff' => [],
    ],
];
