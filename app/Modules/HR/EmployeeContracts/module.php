<?php

use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractSnapshotReader;
use App\Modules\HR\EmployeeContracts\Providers\EmployeeContractsServiceProvider;

return [
    'name' => 'EmployeeContracts', 'project' => 'HR', 'title' => 'Employee Contracts',
    'slug' => 'employee-contracts', 'description' => 'Effective-dated employee contract history.',
    'version' => '1.0.0', 'enabled' => true,
    'providers' => [EmployeeContractsServiceProvider::class],
    'dependencies' => ['Employees', 'EmploymentTypes'],
    'exports' => ['routes' => true, 'permissions' => true, 'navigation' => true],
    'commands' => ['hr:contracts-expiring'],
    'events' => ['EmployeeContractCreated', 'EmployeeContractActivated', 'EmployeeContractTerminated', 'EmployeeContractCancelled', 'EmployeeContractSuperseded', 'EmployeeContractArchived', 'EmployeeContractRestored'], 'listeners' => [],
    'integrations' => [
        'upstream_for' => ['Payroll'],
        'depends_on' => ['HR.Employees', 'HR.EmploymentTypes'],
        'contracts' => [[
            'name' => 'EmployeeContractSnapshot',
            'schema_version' => 1,
            'reader' => EmployeeContractSnapshotReader::class,
            'schema' => 'Integration/Schemas/employee-contract-snapshot-v1.json',
        ]],
    ],
];
