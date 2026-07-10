<?php

return [
    'group' => 'HR',
    'sort' => 170,
    'items' => [
        [
            'title' => 'Organization Structures',
            'url' => '/hr/organization-structures',
            'icon' => 'Network',
            'permissions' => ['hr.view', 'organization-structures.view', 'organization-structures.manage'],
        ],
    ],
];
