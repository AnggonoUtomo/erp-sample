<?php

return [
    'group' => 'HR',
    'sort' => 140,
    'items' => [
        [
            'title' => 'Employment Statuses',
            'url' => '/hr/employment-statuses',
            'icon' => 'BadgeCheck',
            'permissions' => ['hr.view', 'employment-statuses.view', 'employment-statuses.manage'],
        ],
    ],
];
