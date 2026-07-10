<?php

return [
    'permissions' => [
        'hr.view',
        'work-locations.view',
        'work-locations.create',
        'work-locations.update',
        'work-locations.delete',
        'work-locations.restore',
        'work-locations.force-delete',
        'work-locations.manage',
    ],
    'roles' => [
        'admin' => ['hr.view', 'work-locations.view', 'work-locations.create', 'work-locations.update', 'work-locations.delete', 'work-locations.restore', 'work-locations.force-delete', 'work-locations.manage'],
        'staff' => ['hr.view', 'work-locations.view'],
    ],
];
