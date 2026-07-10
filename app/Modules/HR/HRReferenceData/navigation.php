<?php

return [
    'group' => 'HR',
    'sort' => 160,
    'items' => [
        [
            'title' => 'HR Reference Data',
            'url' => '/hr/hr-reference-data',
            'icon' => 'ListFilter',
            'permissions' => ['hr.view', 'hr-reference-data.view', 'hr-reference-data.manage'],
        ],
    ],
];
