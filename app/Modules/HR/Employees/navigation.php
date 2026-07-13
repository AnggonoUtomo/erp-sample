<?php

return [
    'group' => 'Employee',
    'sort' => 170,
    'items' => [
        [
            'title' => 'Employees',
            'url' => '/hr/employees',
            'icon' => 'UsersRound',
            'permissions' => ['hr.view', 'employees.view', 'employees.manage'],
        ],
    ],
];
