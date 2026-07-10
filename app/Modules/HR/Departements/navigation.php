<?php

return [
    'group' => 'HR',
    'sort' => 100,
    'items' => [
        [
            'title' => 'Departements',
            'url' => '/hr/departements',
            'icon' => 'Building2',
            'permissions' => ['hr.view', 'departements.view', 'departements.manage'],
        ],
    ],
];
