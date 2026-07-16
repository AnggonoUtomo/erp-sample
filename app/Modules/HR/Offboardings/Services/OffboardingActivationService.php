<?php

namespace App\Modules\HR\Offboardings\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\EmployeeContracts\Models\EmployeeContract;
use App\Modules\HR\Employees\Models\Employee;
use App\Modules\HR\EmploymentStatuses\Models\EmploymentStatus;
use App\Modules\HR\Offboardings\Enums\OffboardingStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Transactions\OffboardingTransaction;
use Illuminate\Validation\ValidationException;

class OffboardingActivationService
{
    public function __construct(
        private readonly OffboardingTransaction $transaction,
        private readonly AuditLogService $audit,
    ) {}

    public function activate(Offboarding $offboarding): Offboarding
    {
        return $this->transaction->run(function () use ($offboarding) {
            $locked = Offboarding::query()
                ->withTrashed()
                ->lockForUpdate()
                ->findOrFail($offboarding->id);

            if ($locked->status === OffboardingStatus::InProgress && ! $locked->trashed()) {
                return $locked;
            }

            $expectedIdentity = "employee:{$locked->employee_id}";
            if ($locked->trashed()
                || $locked->status !== OffboardingStatus::Draft
                || ! is_string($locked->active_identity_key)
                || ! hash_equals($expectedIdentity, $locked->active_identity_key)) {
                $this->reject('Hanya draft aktif dengan employee identity valid yang dapat diaktifkan.');
            }

            $this->assertReferencesAreEligible($locked);
            $this->assertNoOtherActiveCase($locked);

            $locked->update(['status' => OffboardingStatus::InProgress]);
            $this->audit->record(
                module: 'hr.offboardings',
                event: 'Offboarding.activated',
                auditable: $locked,
                description: "Activated offboarding {$locked->id}",
                oldValues: ['status' => OffboardingStatus::Draft->value],
                newValues: ['status' => OffboardingStatus::InProgress->value],
            );

            return $locked->refresh();
        });
    }

    private function assertReferencesAreEligible(Offboarding $offboarding): void
    {
        $employee = Employee::query()
            ->where('active', true)
            ->lockForUpdate()
            ->whereKey($offboarding->employee_id)
            ->first(['id']);
        if (! $employee) {
            $this->reject('Employee aktif tidak tersedia untuk activation.');
        }

        if ($offboarding->employee_contract_id !== null) {
            $contract = EmployeeContract::query()
                ->where('employee_id', $offboarding->employee_id)
                ->where('status', 'ACTIVE')
                ->lockForUpdate()
                ->whereKey($offboarding->employee_contract_id)
                ->first(['id']);
            if (! $contract) {
                $this->reject('Contract aktif tidak lagi sesuai dengan employee.');
            }
        }

        $targetStatus = EmploymentStatus::query()
            ->where('active', true)
            ->where('is_final_status', true)
            ->lockForUpdate()
            ->whereKey($offboarding->target_employment_status_id)
            ->first(['id']);
        if (! $targetStatus) {
            $this->reject('Target employment status final tidak tersedia.');
        }

        $owner = User::query()
            ->lockForUpdate()
            ->whereKey($offboarding->owner_user_id)
            ->first(['id']);
        if (! $owner) {
            $this->reject('Owner offboarding tidak tersedia.');
        }
    }

    private function assertNoOtherActiveCase(Offboarding $offboarding): void
    {
        $conflict = Offboarding::query()
            ->where('employee_id', $offboarding->employee_id)
            ->whereKeyNot($offboarding->id)
            ->whereIn('status', [
                OffboardingStatus::Draft->value,
                OffboardingStatus::InProgress->value,
                OffboardingStatus::ReadyForExit->value,
            ])
            ->lockForUpdate()
            ->first(['id']);

        if ($conflict) {
            $this->reject('Employee ini sudah memiliki offboarding aktif lain.');
        }
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['status' => $message]);
    }
}
