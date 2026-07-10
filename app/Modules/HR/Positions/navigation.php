<?php

return [
    'group' => 'HR',
    'sort' => 110,
    'items' => [
        [
            'title' => 'Positions',
            'url' => '/hr/positions',
            'icon' => 'UserRoundCog',
            'permissions' => ['hr.view', 'positions.view', 'positions.manage'],
        ],
    ],
];
