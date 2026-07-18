<?php

return [
    'group' => 'HR',
    'sort' => 260,
    'items' => [
        [
            'title' => 'HR Reports',
            'url' => '/hr/reports',
            'icon' => 'ListFilter',
            'permissions' => ['hr-reports.view', 'hr-reports.manage'],
        ],
    ],
];
