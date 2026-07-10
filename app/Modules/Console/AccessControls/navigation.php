<?php

return [
    'group' => 'Administrasi',
    'sort' => 50,
    'items' => [
        [
            'title' => 'Kontrol Akses',
            'url' => '/access-control',
            'icon' => 'ShieldCheck',
            'permissions' => ['roles.manage', 'access-control.view'],
        ],
    ],
];
