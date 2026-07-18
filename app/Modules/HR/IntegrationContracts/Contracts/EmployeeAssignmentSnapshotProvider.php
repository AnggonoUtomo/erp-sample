<?php

namespace App\Modules\HR\IntegrationContracts\Contracts;

use App\Modules\HR\IntegrationContracts\DTO\EmployeeAssignmentSnapshotV1;

interface EmployeeAssignmentSnapshotProvider
{
    public function forEmployee(int $employeeId, string $effectiveDate): ?EmployeeAssignmentSnapshotV1;
}
