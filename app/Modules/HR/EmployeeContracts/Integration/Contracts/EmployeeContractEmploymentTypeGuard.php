<?php

namespace App\Modules\HR\EmployeeContracts\Integration\Contracts;

interface EmployeeContractEmploymentTypeGuard
{
    public function hasActiveEffectiveContract(int $employeeId, int $employmentTypeId, string $effectiveDate): bool;
}
