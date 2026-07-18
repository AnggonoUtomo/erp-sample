<?php

namespace App\Modules\HR\IntegrationContracts\Services;

use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeAssignmentSnapshotProvider;
use App\Modules\HR\IntegrationContracts\DTO\EmployeeAssignmentSnapshotV1;
use Illuminate\Database\Eloquent\Model;

class EloquentEmployeeAssignmentSnapshotProvider implements EmployeeAssignmentSnapshotProvider
{
    public function forEmployee(int $employeeId, string $effectiveDate): ?EmployeeAssignmentSnapshotV1
    {
        $employee = Employee::query()
            ->with([
                'departement:id,code,name',
                'position:id,code,name',
                'jobLevel:id,code,name',
                'workLocation:id,code,name',
                'employmentStatus:id,code,name,requires_attendance,included_in_payroll,is_final_status',
                'employmentType:id,code,name,requires_contract_end_date,included_in_payroll,eligible_for_overtime',
            ])
            ->whereKey($employeeId)
            ->first(['id', 'departement_id', 'position_id', 'job_level_id', 'work_location_id', 'employment_status_id', 'employment_type_id']);

        if (! $employee) {
            return null;
        }

        return new EmployeeAssignmentSnapshotV1(
            employeeId: $employee->id,
            effectiveDate: $effectiveDate,
            departement: $this->basicReference($employee->departement),
            position: $this->basicReference($employee->position),
            jobLevel: $this->basicReference($employee->jobLevel),
            workLocation: $this->basicReference($employee->workLocation),
            employmentStatus: $this->employmentStatusReference($employee->employmentStatus),
            employmentType: $this->employmentTypeReference($employee->employmentType),
        );
    }

    /** @return array{id: int, code: string, name: string}|null */
    private function basicReference(?Model $model): ?array
    {
        if (! $model) {
            return null;
        }

        return [
            'id' => $model->id,
            'code' => (string) $model->getAttribute('code'),
            'name' => (string) $model->getAttribute('name'),
        ];
    }

    /** @return array{id: int, code: string, name: string, requiresAttendance: bool, includedInPayroll: bool, isTerminal: bool}|null */
    private function employmentStatusReference(?Model $model): ?array
    {
        $reference = $this->basicReference($model);

        if ($reference === null) {
            return null;
        }

        return [
            ...$reference,
            'requiresAttendance' => (bool) $model->getAttribute('requires_attendance'),
            'includedInPayroll' => (bool) $model->getAttribute('included_in_payroll'),
            'isTerminal' => (bool) $model->getAttribute('is_final_status'),
        ];
    }

    /** @return array{id: int, code: string, name: string, requiresContractEndDate: bool, includedInPayroll: bool, eligibleForOvertime: bool}|null */
    private function employmentTypeReference(?Model $model): ?array
    {
        $reference = $this->basicReference($model);

        if ($reference === null) {
            return null;
        }

        return [
            ...$reference,
            'requiresContractEndDate' => (bool) $model->getAttribute('requires_contract_end_date'),
            'includedInPayroll' => (bool) $model->getAttribute('included_in_payroll'),
            'eligibleForOvertime' => (bool) $model->getAttribute('eligible_for_overtime'),
        ];
    }
}
