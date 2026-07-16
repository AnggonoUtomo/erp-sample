<?php

return [
    'group' => 'Employee',
    'sort' => 200,
    'items' => [
        [
            'title' => 'Employee Onboardings',
            'url' => '/hr/onboardings',
            'icon' => 'ClipboardCheck',
            'permissions' => ['onboardings.view', 'onboardings.create', 'onboardings.template-manage', 'onboardings.manage'],
            'children' => [
                [
                    'title' => 'Daftar Onboardings',
                    'url' => '/hr/onboardings',
                    'icon' => 'ClipboardCheck',
                    'exact' => true,
                    'permissions' => ['onboardings.view', 'onboardings.create', 'onboardings.manage'],
                ],
                [
                    'title' => 'Onboarding Templates',
                    'url' => '/hr/onboardings/templates',
                    'icon' => 'ListChecks',
                    'permissions' => ['onboardings.view', 'onboardings.template-manage', 'onboardings.manage'],
                ],
            ],
        ],
    ],
];
