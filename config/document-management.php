<?php

return [
    'storage_disk' => env('DMS_PRIVATE_DISK', 'dms-private'),
    'ingestion_enabled' => env(
        'DMS_INGESTION_ENABLED',
        in_array(strtolower((string) env('APP_ENV', 'production')), ['local', 'testing'], true),
    ),
    'upload' => [
        'max_bytes' => 20 * 1024 * 1024,
        'allowed_types' => [
            'pdf' => 'application/pdf',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
        ],
        'scan_status' => 'NOT_CONFIGURED',
        'staged_cleanup_hours' => 24,
    ],
];
