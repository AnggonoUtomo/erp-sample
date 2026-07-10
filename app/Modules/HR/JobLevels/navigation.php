<?php

return [
    'group' => 'HR',
    'sort' => 120,
    'items' => [
        [
            'title' => 'Job Levels',
            'url' => '/hr/job-levels',
            'icon' => 'Layers',
            'permissions' => ['hr.view', 'job-levels.view', 'job-levels.manage'],
        ],
    ],
];
