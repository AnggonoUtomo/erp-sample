<?php

return [
    'group' => 'HR',
    'sort' => 130,
    'items' => [
        [
            'title' => 'Work Locations',
            'url' => '/hr/work-locations',
            'icon' => 'MapPin',
            'permissions' => ['hr.view', 'work-locations.view', 'work-locations.manage'],
        ],
    ],
];
