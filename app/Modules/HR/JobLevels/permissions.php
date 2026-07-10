<?php

return [
    'permissions' => [
        'hr.view',
        'job-levels.view',
        'job-levels.create',
        'job-levels.update',
        'job-levels.delete',
        'job-levels.restore',
        'job-levels.force-delete',
        'job-levels.manage',
    ],
    'roles' => [
        'admin' => ['hr.view', 'job-levels.view', 'job-levels.create', 'job-levels.update', 'job-levels.delete', 'job-levels.restore', 'job-levels.force-delete', 'job-levels.manage'],
        'staff' => ['hr.view', 'job-levels.view'],
    ],
];
