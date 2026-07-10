<?php

use App\Modules\Console\BackupRestores\Providers\BackupRestoresServiceProvider;

return [
    'name' => 'BackupRestores',
    'project' => 'Console',
    'title' => 'Backup & Restore',
    'slug' => 'backup-restores',
    'description' => 'Backup dan restore setting, database, serta arsip server.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        BackupRestoresServiceProvider::class,
    ],
    'dependencies' => [],
    'exports' => [
        'routes' => true,
        'permissions' => true,
        'navigation' => true,
    ],
    'events' => [],
    'listeners' => [],
    'integrations' => [],
];
