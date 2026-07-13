<?php

return [
    'permissions' => ['employee-contracts.view', 'employee-contracts.create', 'employee-contracts.manage'],
    'roles' => [
        'admin' => ['hr.view', 'employee-contracts.view', 'employee-contracts.create', 'employee-contracts.manage'],
        'hr-manager' => ['hr.view', 'employee-contracts.view', 'employee-contracts.create', 'employee-contracts.manage'],
        'hr-officer' => ['hr.view', 'employee-contracts.view', 'employee-contracts.create'],
        'hr-viewer' => ['hr.view', 'employee-contracts.view'],
    ],
];
