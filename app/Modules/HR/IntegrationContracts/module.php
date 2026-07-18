<?php

use App\Modules\HR\IntegrationContracts\Providers\HRIntegrationContractsServiceProvider;

return [
    'name' => 'IntegrationContracts',
    'project' => 'HR',
    'title' => 'HR Integration Contracts',
    'slug' => 'integration-contracts',
    'description' => 'Kontrak teknis HR untuk snapshot dan event internal lintas module tanpa UI atau mutation.',
    'version' => '1.0.0',
    'enabled' => true,
    'providers' => [
        HRIntegrationContractsServiceProvider::class,
    ],
    'commands' => [],
    'dependencies' => [
        'HR.Employees',
        'HR.EmployeeContracts',
        'HR.EmployeeDocuments',
        'HR.EmployeeMovements',
        'HR.Onboardings',
        'HR.Offboardings',
    ],
    'exports' => [
        'routes' => false,
        'permissions' => true,
        'navigation' => false,
    ],
    'events' => [],
    'listeners' => [],
    'integrations' => [
        'boundary' => 'contract-only',
        'read_models' => [
            'EmployeeSnapshotV1',
            'EmployeeAssignmentSnapshotV1',
            'EmployeeContractSnapshotV1',
            'EmployeeDocumentComplianceSnapshotV1',
        ],
        'events' => [
            'EmployeeCreatedV1',
            'EmployeeProfileUpdatedV1',
            'EmployeeAssignmentChangedV1',
            'EmployeeContractChangedV1',
            'EmployeeDocumentComplianceChangedV1',
            'EmployeeOnboardingActivatedV1',
            'EmployeeOnboardingCompletedV1',
            'EmployeeOffboardingReadyV1',
            'EmployeeOffboardingFinalizedV1',
            'EmploymentTerminatedV1',
        ],
        'deferred' => [
            'public_api',
            'webhooks',
            'queue_outbox',
            'downstream_listeners',
            'integration_event_log',
        ],
    ],
];
