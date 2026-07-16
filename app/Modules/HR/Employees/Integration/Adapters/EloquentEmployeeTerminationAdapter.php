<?php

namespace App\Modules\HR\Employees\Integration\Adapters;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Employees\Integration\Contracts\EmployeeTerminationGateway;
use App\Modules\HR\Employees\Integration\DTO\EmployeeTerminationCommandV1;
use App\Modules\HR\Employees\Integration\DTO\EmployeeTerminationResultV1;
use App\Modules\HR\Employees\Integration\Exceptions\EmployeeTerminationRejected;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\Employees\Transactions\EmployeesTransaction;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use Carbon\CarbonImmutable;

final class EloquentEmployeeTerminationAdapter implements EmployeeTerminationGateway
{
    public function __construct(
        private readonly EmployeesTransaction $transaction,
        private readonly AuditLogService $auditLog,
    ) {}

    public function terminate(EmployeeTerminationCommandV1 $command): EmployeeTerminationResultV1
    {
        return $this->transaction->run(function () use ($command): EmployeeTerminationResultV1 {
            $employee = Employee::withTrashed()->lockForUpdate()->find($command->employeeId);
            $actor = User::query()->lockForUpdate()->find($command->actorUserId);
            $targetStatus = EmploymentStatus::withTrashed()->lockForUpdate()->find($command->targetEmploymentStatusId);

            if (
                ! $employee
                || $employee->trashed()
                || $command->expectedState !== 'ACTIVE'
                || ! $employee->active
                || $employee->ended_at !== null
                || ! $actor
                || ! $targetStatus
                || $targetStatus->trashed()
                || ! $targetStatus->active
                || ! $targetStatus->is_final_status
                || ($employee->hired_at && CarbonImmutable::parse($command->effectiveDate)->lt($employee->hired_at))
            ) {
                throw EmployeeTerminationRejected::forInvariantViolation();
            }

            $previousEmploymentStatusId = $employee->employment_status_id;
            $employee->update([
                'employment_status_id' => $targetStatus->id,
                'ended_at' => $command->effectiveDate,
                'active' => false,
            ]);

            $this->auditLog->record(
                module: 'HR.Employees',
                event: 'Employee.terminated-via-boundary',
                auditable: $employee,
                description: 'Employee employment was terminated through the owner boundary.',
                oldValues: [
                    'employment_status_id' => $previousEmploymentStatusId,
                    'active' => true,
                    'ended_at' => null,
                ],
                newValues: [
                    'employment_status_id' => $targetStatus->id,
                    'active' => false,
                    'ended_at' => $command->effectiveDate,
                    'reason_recorded' => true,
                ],
                actor: $actor,
                fallbackToAuthenticatedActor: false,
                throwOnFailure: true,
            );

            return new EmployeeTerminationResultV1(
                employeeId: $employee->id,
                previousEmploymentStatusId: $previousEmploymentStatusId,
                employmentStatusId: $targetStatus->id,
                effectiveDate: $command->effectiveDate,
            );
        });
    }
}
