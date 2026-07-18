<?php

return [
    'group' => 'Employee',
    'sort' => 142,
    'items' => [
        [
            'title' => 'Employment Statuses',
            'url' => '/hr/employment-statuses',
            'icon' => 'BadgeCheck',
            'permissions' => ['hr.view', 'employment-statuses.view', 'employment-statuses.manage'],
        ],
    ],
];
