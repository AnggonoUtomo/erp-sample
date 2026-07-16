<?php

namespace App\Modules\HR\Offboardings\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\Integration\Contracts\EmployeeContractTerminationGateway;
use App\Modules\HR\EmployeeContracts\Integration\DTO\EmployeeContractTerminationCommandV1;
use App\Modules\HR\EmployeeContracts\Integration\Exceptions\EmployeeContractTerminationRejected;
use App\Modules\HR\Employees\Integration\Contracts\EmployeeTerminationGateway;
use App\Modules\HR\Employees\Integration\DTO\EmployeeTerminationCommandV1;
use App\Modules\HR\Employees\Integration\Exceptions\EmployeeTerminationRejected;
use App\Modules\HR\Offboardings\DTO\OffboardingFinalizationData;
use App\Modules\HR\Offboardings\Enums\OffboardingStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTask;
use App\Modules\HR\Offboardings\Transactions\OffboardingTransaction;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class OffboardingFinalizationService
{
    public function __construct(
        private readonly OffboardingTransaction $transaction,
        private readonly EmployeeTerminationGateway $employees,
        private readonly EmployeeContractTerminationGateway $contracts,
        private readonly AuditLogService $audit,
    ) {}

    public function finalize(Offboarding $offboarding, OffboardingFinalizationData $data): Offboarding
    {
        return $this->transaction->run(function () use ($offboarding, $data): Offboarding {
            $locked = Offboarding::query()
                ->withTrashed()
                ->lockForUpdate()
                ->findOrFail($offboarding->id);

            if ($locked->status === OffboardingStatus::Completed && ! $locked->trashed()) {
                return $locked;
            }

            if ($locked->trashed() || $locked->status !== OffboardingStatus::ReadyForExit) {
                $this->reject('Finalisasi hanya tersedia untuk offboarding yang siap keluar.');
            }

            $businessDate = CarbonImmutable::createFromFormat('!Y-m-d', $data->businessDate);
            if ($businessDate->lt($locked->exit_date)) {
                throw ValidationException::withMessages([
                    'business_date' => 'Finalisasi tidak dapat dilakukan sebelum tanggal keluar.',
                ]);
            }

            try {
                $this->employees->terminate(new EmployeeTerminationCommandV1(
                    employeeId: $locked->employee_id,
                    expectedState: 'ACTIVE',
                    targetEmploymentStatusId: $locked->target_employment_status_id,
                    effectiveDate: $locked->exit_date->format('Y-m-d'),
                    reason: $locked->exit_reason,
                    actorUserId: $data->actorUserId,
                ));

                if ($locked->employee_contract_id !== null) {
                    $this->contracts->terminate(new EmployeeContractTerminationCommandV1(
                        contractId: $locked->employee_contract_id,
                        employeeId: $locked->employee_id,
                        expectedState: 'ACTIVE',
                        effectiveDate: $locked->exit_date->format('Y-m-d'),
                        reason: $locked->exit_reason,
                        actorUserId: $data->actorUserId,
                    ));
                }
            } catch (EmployeeTerminationRejected|EmployeeContractTerminationRejected) {
                $this->reject('Data employment berubah dan finalisasi perlu diperiksa ulang.');
            }

            $tasks = OffboardingTask::query()
                ->where('offboarding_id', $locked->id)
                ->lockForUpdate()
                ->get();
            if ($tasks->contains(fn (OffboardingTask $task) => $task->required && ! $task->status->isTerminal())) {
                $this->reject('Task wajib belum selesai.');
            }

            $actor = User::query()->find($data->actorUserId);
            if (! $actor) {
                $this->reject('Finalisasi offboarding ditolak.');
            }

            $locked->update([
                'status' => OffboardingStatus::Completed,
                'active_identity_key' => null,
                'finalized_by_user_id' => $data->actorUserId,
                'finalized_at' => now(),
                'finalization_business_date' => $data->businessDate,
            ]);
            $this->audit->record(
                module: 'hr.offboardings',
                event: 'Offboarding.finalized',
                auditable: $locked,
                description: "Finalized offboarding {$locked->id}",
                oldValues: ['status' => OffboardingStatus::ReadyForExit->value],
                newValues: [
                    'status' => OffboardingStatus::Completed->value,
                    'finalized_by_user_id' => $data->actorUserId,
                    'business_date' => $data->businessDate,
                ],
                actor: $actor,
                fallbackToAuthenticatedActor: false,
                throwOnFailure: true,
            );

            return $locked->refresh();
        });
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['status' => $message]);
    }
}
