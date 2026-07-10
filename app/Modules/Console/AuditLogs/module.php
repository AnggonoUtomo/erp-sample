<?php

use App\Modules\Console\AuditLogs\Providers\AuditLogsServiceProvider;

return [
    'name' => 'AuditLogs',
    'project' => 'Console',
    'title' => 'Audit Logs',
    'slug' => 'audit-logs',
    'description' => 'Pencatatan jejak audit untuk aksi penting.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        AuditLogsServiceProvider::class,
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
