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
use App\Modules\HR\Offboardings\Integration\Events\EmployeeOffboardingCompletedV1;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTask;
use App\Modules\HR\Offboardings\Transactions\OffboardingTransaction;
use App\Shared\Contracts\DomainEventDispatcher;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class OffboardingFinalizationService
{
    private const GENERIC_REJECTION = 'Finalisasi offboarding ditolak. Periksa kembali kesiapan dan data employment.';

    public function __construct(
        private readonly OffboardingTransaction $transaction,
        private readonly EmployeeTerminationGateway $employees,
        private readonly EmployeeContractTerminationGateway $contracts,
        private readonly AuditLogService $audit,
        private readonly DomainEventDispatcher $events,
    ) {}

    public function finalize(Offboarding $offboarding, OffboardingFinalizationData $data): Offboarding
    {
        $shouldPublishCompletedEvent = false;

        $finalized = $this->transaction->run(function () use ($offboarding, $data, &$shouldPublishCompletedEvent): Offboarding {
            $locked = Offboarding::query()
                ->withTrashed()
                ->lockForUpdate()
                ->findOrFail($offboarding->id);

            if ($locked->status === OffboardingStatus::Completed && ! $locked->trashed()) {
                return $locked;
            }

            if ($locked->trashed() || $locked->status !== OffboardingStatus::ReadyForExit) {
                $this->reject();
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
                $this->reject();
            }

            $tasks = OffboardingTask::query()
                ->where('offboarding_id', $locked->id)
                ->lockForUpdate()
                ->get();
            if ($tasks->contains(fn (OffboardingTask $task) => $task->required && ! $task->status->isTerminal())) {
                $this->reject();
            }

            $actor = User::query()->find($data->actorUserId);
            if (! $actor) {
                $this->reject();
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

            $shouldPublishCompletedEvent = true;

            return $locked->refresh();
        });

        if ($shouldPublishCompletedEvent) {
            $this->events->dispatch(EmployeeOffboardingCompletedV1::fromOffboarding($finalized));
        }

        return $finalized;
    }

    private function reject(): never
    {
        throw ValidationException::withMessages(['status' => self::GENERIC_REJECTION]);
    }
}
