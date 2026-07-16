<?php

return [
    'group' => 'Employee',
    'sort' => 210,
    'items' => [
        [
            'title' => 'Employee Offboardings',
            'url' => '/hr/offboardings',
            'icon' => 'LogOut',
            'permissions' => [
                'offboardings.view',
                'offboardings.create',
                'offboardings.template-manage',
                'offboardings.manage',
            ],
            'children' => [
                [
                    'title' => 'Daftar Offboardings',
                    'url' => '/hr/offboardings',
                    'icon' => 'LogOut',
                    'exact' => true,
                    'permissions' => [
                        'offboardings.view',
                        'offboardings.create',
                        'offboardings.manage',
                    ],
                ],
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
        ],
    ],
];
