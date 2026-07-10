<?php

return [
    'group' => 'Administrasi',
    'sort' => 40,
    'items' => [
        [
            'title' => 'Manajemen User',
            'url' => '/users',
            'icon' => 'Users',
            'permissions' => ['users.view'],
        ],
    ],
];
