<?php

namespace App\Modules\HR\IntegrationContracts\Contracts;

use App\Modules\HR\IntegrationContracts\DTO\EmployeeContractSnapshotV1;

interface EmployeeContractSnapshotProvider
{
    public function forEmployee(int $employeeId, string $asOf): ?EmployeeContractSnapshotV1;
}
