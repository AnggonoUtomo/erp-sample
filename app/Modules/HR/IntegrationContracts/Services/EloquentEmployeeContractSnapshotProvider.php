<?php

namespace App\Modules\HR\IntegrationContracts\Services;

use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeContractSnapshotProvider;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeContractSnapshotV1;

class EloquentEmployeeContractSnapshotProvider implements EmployeeContractSnapshotProvider
{
    public function forEmployee(int $employeeId, string $asOf): ?EmployeeContractSnapshotV1
    {
        if (! Employee::query()->whereKey($employeeId)->exists()) {
            return null;
        }

        $contract = EmployeeContract::query()
            ->with('employmentType:id,code')
            ->forEmployee($employeeId)
            ->effectiveOn($asOf)
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first(['id', 'employee_id', 'employment_type_id', 'start_date', 'end_date', 'status']);

        $isCurrent = true;

        if (! $contract) {
            $contract = EmployeeContract::query()
                ->with('employmentType:id,code')
                ->forEmployee($employeeId)
                ->where('status', '!=', 'CANCELLED')
                ->orderByDesc('start_date')
                ->orderByDesc('id')
                ->first(['id', 'employee_id', 'employment_type_id', 'start_date', 'end_date', 'status']);
            $isCurrent = false;
        }

        if (! $contract) {
            return null;
        }

        return new EmployeeContractSnapshotV1(
            employeeId: $contract->employee_id,
            contractId: $contract->id,
            contractType: (string) $contract->employmentType?->code,
            status: $contract->status,
            startDate: $contract->start_date->toDateString(),
            endDate: $contract->end_date?->toDateString(),
            isCurrent: $isCurrent,
        );
    }
}
