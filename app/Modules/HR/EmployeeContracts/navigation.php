<?php

return [
    'group' => 'HR',
    'sort' => 180,
    'items' => [
        [
            'title' => 'Employee Contracts',
            'url' => '/hr/employee-contracts',
            'icon' => 'FileSignature',
            'permissions' => ['employee-contracts.view', 'employee-contracts.manage'],
        ],
    ],
];
