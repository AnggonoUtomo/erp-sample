<?php

namespace App\Modules\HR\EmployeeContracts\Integration\Adapters;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractTerminationGateway;
use App\Modules\HR\EmployeeContracts\Integration\DTO\EmployeeContractTerminationCommandV1;
use App\Modules\HR\EmployeeContracts\Integration\DTO\EmployeeContractTerminationResultV1;
use App\Modules\HR\EmployeeContracts\Integration\Exceptions\EmployeeContractTerminationRejected;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\EmployeeContracts\Transactions\EmployeeContractsTransaction;
use Carbon\CarbonImmutable;

final class EloquentEmployeeContractTerminationAdapter implements EmployeeContractTerminationGateway
{
    public function __construct(
        private readonly EmployeeContractsTransaction $transaction,
        private readonly AuditLogService $auditLog,
    ) {}

    public function terminate(EmployeeContractTerminationCommandV1 $command): EmployeeContractTerminationResultV1
    {
        return $this->transaction->run(function () use ($command): EmployeeContractTerminationResultV1 {
            $contract = EmployeeContract::withTrashed()->lockForUpdate()->find($command->contractId);
            $actor = User::query()->lockForUpdate()->find($command->actorUserId);

            if (
                ! $contract
                || $contract->trashed()
                || ! $actor
                || $command->expectedState !== 'ACTIVE'
                || $contract->status !== 'ACTIVE'
                || $contract->employee_id !== $command->employeeId
                || CarbonImmutable::parse($command->effectiveDate)->lt($contract->start_date)
            ) {
                throw EmployeeContractTerminationRejected::forInvariantViolation();
            }

            $contract->update([
                'status' => 'ENDED',
                'end_date' => $command->effectiveDate,
                'ended_reason' => $command->reason,
            ]);

            $this->auditLog->record(
                module: 'HR.EmployeeContracts',
                event: 'EmployeeContract.terminated-via-boundary',
                auditable: $contract,
                description: 'Employee contract was ended through the owner boundary.',
                oldValues: ['status' => 'ACTIVE', 'end_date' => null],
                newValues: [
                    'status' => 'ENDED',
                    'end_date' => $command->effectiveDate,
                    'reason_recorded' => true,
                ],
                actor: $actor,
                fallbackToAuthenticatedActor: false,
                throwOnFailure: true,
            );

            return new EmployeeContractTerminationResultV1(
                contractId: $contract->id,
                employeeId: $contract->employee_id,
                effectiveDate: $command->effectiveDate,
            );
        });
    }
}
