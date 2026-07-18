<?php

namespace App\Modules\HR\IntegrationContracts\Support;

class HRIntegrationEventRegistry
{
    /**
     * @return array<string, array{sourceModule: string, publisher: ?string, state: string}>
     */
    public function events(): array
    {
        return [
            'EmployeeCreatedV1' => [
                'sourceModule' => 'HR.Employees',
                'publisher' => null,
                'state' => 'deferred_until_employee_publisher_task',
            ],
            'EmployeeProfileUpdatedV1' => [
                'sourceModule' => 'HR.Employees',
                'publisher' => null,
                'state' => 'deferred_until_employee_publisher_task',
            ],
            'EmployeeAssignmentChangedV1' => [
                'sourceModule' => 'HR.EmployeeMovements',
                'publisher' => 'App\\Modules\\HR\\EmployeeMovements\\Integration\\Events\\EmployeeMovementAppliedV1',
                'state' => 'existing_source_event_mapped',
            ],
            'EmployeeContractChangedV1' => [
                'sourceModule' => 'HR.EmployeeContracts',
                'publisher' => null,
                'state' => 'deferred_until_contract_publisher_task',
            ],
            'EmployeeDocumentComplianceChangedV1' => [
                'sourceModule' => 'HR.EmployeeDocuments',
                'publisher' => null,
                'state' => 'deferred_until_document_publisher_task',
            ],
            'EmployeeOnboardingActivatedV1' => [
                'sourceModule' => 'HR.Onboardings',
                'publisher' => null,
                'state' => 'deferred_until_onboarding_publisher_task',
            ],
            'EmployeeOnboardingCompletedV1' => [
                'sourceModule' => 'HR.Onboardings',
                'publisher' => null,
                'state' => 'deferred_until_onboarding_publisher_task',
            ],
            'EmployeeOffboardingReadyV1' => [
                'sourceModule' => 'HR.Offboardings',
                'publisher' => null,
                'state' => 'deferred_until_offboarding_ready_publisher_task',
            ],
            'EmployeeOffboardingFinalizedV1' => [
                'sourceModule' => 'HR.Offboardings',
                'publisher' => 'App\\Modules\\HR\\Offboardings\\Integration\\Events\\EmployeeOffboardingCompletedV1',
                'state' => 'existing_source_event_mapped',
            ],
            'EmploymentTerminatedV1' => [
                'sourceModule' => 'HR.Offboardings',
                'publisher' => 'App\\Modules\\HR\\Offboardings\\Integration\\Events\\EmployeeOffboardingCompletedV1',
                'state' => 'existing_source_event_mapped',
            ],
        ];
    }

    public function sourceModuleFor(string $eventName): ?string
    {
        return $this->events()[$eventName]['sourceModule'] ?? null;
    }

    public function has(string $eventName): bool
    {
        return array_key_exists($eventName, $this->events());
    }
}
