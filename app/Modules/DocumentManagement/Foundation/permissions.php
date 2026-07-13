<?php

return [
    'permissions' => [
        'document-management.view',
        'documents.view',
        'documents.upload',
        'documents.replace',
        'documents.download',
    ],
    'roles' => [
        'admin' => [
            'document-management.view',
            'documents.view',
            'documents.upload',
            'documents.replace',
            'documents.download',
        ],
    ],
];
