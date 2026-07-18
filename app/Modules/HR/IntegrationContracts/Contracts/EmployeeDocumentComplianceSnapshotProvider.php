<?php

namespace App\Modules\HR\IntegrationContracts\Contracts;

use App\Modules\HR\IntegrationContracts\DTO\EmployeeDocumentComplianceSnapshotV1;

interface EmployeeDocumentComplianceSnapshotProvider
{
    public function forEmployee(int $employeeId, string $asOf, int $warningDays = 30): ?EmployeeDocumentComplianceSnapshotV1;
}
