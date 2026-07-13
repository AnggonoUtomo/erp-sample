<?php

return [
    'storage_disk' => env('DMS_PRIVATE_DISK', 'dms-private'),
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
