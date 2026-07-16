<?php

$managerPermissions = [
    'hr.view',
    'offboardings.view',
    'offboardings.template-manage',
    'offboardings.create',
    'offboardings.activate',
    'offboardings.task-update',
    'offboardings.task-skip-required',
    'offboardings.mark-ready',
    'offboardings.finalize',
    'offboardings.cancel',
    'offboardings.archive',
    'offboardings.restore',
    'offboardings.manage',
];

return [
    'permissions' => [
        'offboardings.view',
        'offboardings.template-manage',
        'offboardings.create',
        'offboardings.activate',
        'offboardings.task-update',
        'offboardings.task-skip-required',
        'offboardings.mark-ready',
        'offboardings.finalize',
        'offboardings.cancel',
        'offboardings.archive',
        'offboardings.restore',
        'offboardings.manage',
    ],
    'roles' => [
        'admin' => $managerPermissions,
        'hr-manager' => $managerPermissions,
        'hr-officer' => [
            'hr.view',
            'offboardings.view',
            'offboardings.create',
            'offboardings.task-update',
        ],
        'hr-viewer' => [
            'hr.view',
            'offboardings.view',
        ],
    ],
];
