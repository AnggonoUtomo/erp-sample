<?php

return [
    'group' => 'HR',
    'sort' => 150,
    'items' => [
        [
            'title' => 'Employment Types',
            'url' => '/hr/employment-types',
            'icon' => 'BriefcaseBusiness',
            'permissions' => ['hr.view', 'employment-types.view', 'employment-types.manage'],
        ],
    ],
];
