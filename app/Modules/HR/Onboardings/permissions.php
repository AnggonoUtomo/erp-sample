<?php

return [
    'permissions' => [
        'onboardings.view',
        'onboardings.template-manage',
        'onboardings.create',
        'onboardings.activate',
        'onboardings.task-update',
        'onboardings.complete',
        'onboardings.cancel',
        'onboardings.archive',
        'onboardings.restore',
        'onboardings.manage',
    ],
    'roles' => [
        'admin' => [
            'hr.view',
            'onboardings.view',
            'onboardings.template-manage',
            'onboardings.create',
            'onboardings.activate',
            'onboardings.task-update',
            'onboardings.complete',
            'onboardings.cancel',
            'onboardings.archive',
            'onboardings.restore',
            'onboardings.manage',
        ],
        'hr-manager' => [
            'hr.view',
            'onboardings.view',
            'onboardings.template-manage',
            'onboardings.create',
            'onboardings.activate',
            'onboardings.task-update',
            'onboardings.complete',
            'onboardings.cancel',
            'onboardings.archive',
            'onboardings.restore',
            'onboardings.manage',
        ],
        'hr-officer' => [
            'hr.view',
            'onboardings.view',
            'onboardings.create',
            'onboardings.task-update',
        ],
        'hr-viewer' => [
            'hr.view',
            'onboardings.view',
        ],
    ],
];
