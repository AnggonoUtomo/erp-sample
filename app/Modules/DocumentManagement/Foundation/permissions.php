<?php

return [
    'permissions' => [
        'document-management.view',
        'documents.view',
        'documents.upload',
        'documents.replace',
        'documents.archive',
        'documents.restore',
        'documents.download',
    ],
    'roles' => [
        'admin' => [
            'document-management.view',
            'documents.view',
            'documents.upload',
            'documents.replace',
            'documents.archive',
            'documents.restore',
            'documents.download',
        ],
    ],
];
