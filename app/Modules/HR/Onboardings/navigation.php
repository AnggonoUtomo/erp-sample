<?php

return [
    'group' => 'Employee',
    'sort' => 200,
    'items' => [
        [
            'title' => 'Onboarding Templates',
            'url' => '/hr/onboardings/templates',
            'icon' => 'ListChecks',
            'permissions' => ['onboardings.view', 'onboardings.template-manage', 'onboardings.manage'],
        ],
    ],
];
