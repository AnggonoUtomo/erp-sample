<?php

namespace App\Modules\HR\IntegrationContracts\Services;

use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeSnapshotProvider;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeSnapshotV1;

class EloquentEmployeeSnapshotProvider implements EmployeeSnapshotProvider
{
    public function forEmployee(int $employeeId): ?EmployeeSnapshotV1
    {
        $employee = Employee::query()
            ->whereKey($employeeId)
            ->first(['id', 'user_id', 'employee_number', 'display_name', 'work_email', 'active']);

        if (! $employee) {
            return null;
        }

        return new EmployeeSnapshotV1(
            employeeId: $employee->id,
            employeeNumber: $employee->employee_number,
            displayName: $employee->display_name,
            workEmail: $employee->work_email,
            isActive: (bool) $employee->active,
            linkedUserId: $employee->user_id,
        );
    }
}
