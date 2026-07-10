<?php

return [
    'permissions' => [
        'backup-restore.view',
        'backup-restore.export',
        'backup-restore.restore',
        'backup-restore.full-export',
        'backup-restore.full-restore',
    ],
    'roles' => [
        'admin' => ['backup-restore.view', 'backup-restore.export'],
        'staff' => [],
    ],
];
