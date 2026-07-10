<?php

return [
    'group' => 'Operasional',
    'sort' => 75,
    'items' => [
        [
            'title' => 'Queue Monitor',
            'url' => '/queue-monitor',
            'icon' => 'ListRestart',
            'permissions' => ['queue-monitor.view'],
        ],
    ],
];
