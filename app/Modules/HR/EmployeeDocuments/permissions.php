<?php

return [
    'permissions' => [
        'employee-documents.view',
        'employee-documents.create',
        'employee-documents.update',
        'employee-documents.verify',
        'employee-documents.archive',
        'employee-documents.restore',
        'employee-documents.attach',
        'employee-documents.manage',
    ],
    'roles' => [
        'admin' => ['hr.view', 'employee-documents.view', 'employee-documents.create', 'employee-documents.update', 'employee-documents.verify', 'employee-documents.archive', 'employee-documents.restore', 'employee-documents.attach', 'employee-documents.manage'],
        'hr-manager' => ['hr.view', 'employee-documents.view', 'employee-documents.create', 'employee-documents.update', 'employee-documents.verify', 'employee-documents.archive', 'employee-documents.restore', 'employee-documents.attach', 'employee-documents.manage'],
        'hr-officer' => ['hr.view', 'employee-documents.view', 'employee-documents.create', 'employee-documents.update'],
        'hr-viewer' => ['hr.view', 'employee-documents.view'],
    ],
];
