<?php

namespace App\Modules\HR\IntegrationContracts\Contracts;

use App\Modules\HR\IntegrationContracts\DTO\EmployeeSnapshotV1;

interface EmployeeSnapshotProvider
{
    public function forEmployee(int $employeeId): ?EmployeeSnapshotV1;
}
