<?php

namespace App\Modules\HR\EmployeeContracts\Integration\Contracts;

use App\Modules\HR\EmployeeContracts\Integration\DTO\EmployeeContractSnapshotV1;
use Carbon\CarbonImmutable;

interface EmployeeContractSnapshotReader
{
    public function forEmployeeOn(
        int $employeeId,
        CarbonImmutable $effectiveDate,
        CarbonImmutable $capturedAt,
    ): ?EmployeeContractSnapshotV1;
}
