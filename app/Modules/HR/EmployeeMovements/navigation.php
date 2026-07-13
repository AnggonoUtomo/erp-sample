<?php

return [
    'group' => 'Employee',
    'sort' => 190,
    'items' => [
        [
            'title' => 'Employee Movements',
            'url' => '/hr/employee-movements',
            'icon' => 'ListRestart',
            'permissions' => ['employee-movements.view', 'employee-movements.manage'],
        ],
    ],
];
