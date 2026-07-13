<?php

return [
    'group' => 'Employee',
    'sort' => 170,
    'items' => [
        [
            'title' => 'Employee Documents',
            'url' => '/hr/employee-documents',
            'icon' => 'ScrollText',
            'permissions' => ['employee-documents.view', 'employee-documents.manage'],
        ],
    ],
];
