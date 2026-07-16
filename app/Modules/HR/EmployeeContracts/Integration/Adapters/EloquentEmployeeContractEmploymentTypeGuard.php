<?php

namespace App\Modules\HR\EmployeeContracts\Integration\Adapters;

use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractEmploymentTypeGuard;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;

final class EloquentEmployeeContractEmploymentTypeGuard implements EmployeeContractEmploymentTypeGuard
{
    public function hasActiveEffectiveContract(int $employeeId, int $employmentTypeId, string $effectiveDate): bool
    {
        return EmployeeContract::query()
            ->where('employee_id', $employeeId)
            ->where('employment_type_id', $employmentTypeId)
            ->where('status', 'ACTIVE')
            ->whereDate('start_date', '<=', $effectiveDate)
            ->where(fn ($query) => $query->whereNull('end_date')->orWhereDate('end_date', '>=', $effectiveDate))
            ->exists();
    }
}
