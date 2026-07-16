<?php

return [
    'group' => 'Employee',
    'sort' => 210,
    'items' => [
        [
            'title' => 'Offboarding Templates',
            'url' => '/hr/offboardings/templates',
            'icon' => 'ListChecks',
            'permissions' => [
                'offboardings.view',
                'offboardings.template-manage',
                'offboardings.manage',
            ],
        ],
    ],
];
