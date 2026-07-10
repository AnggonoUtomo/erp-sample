<?php

return [
    'group' => 'Observability',
    'sort' => 70,
    'items' => [
        [
            'title' => 'Audit Logs',
            'url' => '/audit-logs',
            'icon' => 'ScrollText',
            'permissions' => ['audit-logs.view'],
        ],
    ],
];
