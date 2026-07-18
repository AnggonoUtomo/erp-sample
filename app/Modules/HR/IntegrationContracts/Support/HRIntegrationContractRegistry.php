<?php

namespace App\Modules\HR\IntegrationContracts\Support;

class HRIntegrationContractRegistry
{
    /** @return array<int, string> */
    public function snapshotContracts(): array
    {
        return [
            'EmployeeSnapshotV1',
            'EmployeeAssignmentSnapshotV1',
            'EmployeeContractSnapshotV1',
            'EmployeeDocumentComplianceSnapshotV1',
        ];
    }

    /** @return array<int, string> */
    public function eventContracts(): array
    {
        return [
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
        ];
    }

    /** @return array{snapshots: array<int, string>, events: array<int, string>} */
    public function all(): array
    {
        return [
            'snapshots' => $this->snapshotContracts(),
            'events' => $this->eventContracts(),
        ];
    }
}
