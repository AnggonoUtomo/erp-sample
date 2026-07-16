<?php

namespace App\Modules\HR\Offboardings\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Offboardings\Enums\OffboardingStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTask;
use App\Modules\HR\Offboardings\Transactions\OffboardingTransaction;
use Illuminate\Validation\ValidationException;

class OffboardingLifecycleService
{
    public function __construct(
        private readonly OffboardingTransaction $transaction,
        private readonly AuditLogService $audit,
    ) {}

    public function markReady(Offboarding $offboarding): Offboarding
    {
        return $this->transaction->run(function () use ($offboarding) {
            $locked = Offboarding::query()
                ->withTrashed()
                ->lockForUpdate()
                ->findOrFail($offboarding->id);

            if ($locked->status === OffboardingStatus::ReadyForExit && ! $locked->trashed()) {
                return $locked;
            }

            if ($locked->trashed() || $locked->status !== OffboardingStatus::InProgress) {
                $this->invalidTransition();
            }

            $tasks = OffboardingTask::query()
                ->where('offboarding_id', $locked->id)
                ->lockForUpdate()
                ->get();
            if ($tasks->contains(
                fn (OffboardingTask $task) => $task->required && ! $task->status->isTerminal(),
            )) {
                throw ValidationException::withMessages([
                    'status' => 'Semua task wajib harus selesai atau di-skip secara sah sebelum offboarding siap difinalisasi.',
                ]);
            }

            $locked->update(['status' => OffboardingStatus::ReadyForExit]);
            $this->audit->record(
                module: 'hr.offboardings',
                event: 'Offboarding.marked_ready',
                auditable: $locked,
                description: "Marked offboarding {$locked->id} ready for exit",
                oldValues: ['status' => OffboardingStatus::InProgress->value],
                newValues: ['status' => OffboardingStatus::ReadyForExit->value],
            );

            return $locked->refresh();
        });
    }

    private function invalidTransition(): never
    {
        throw ValidationException::withMessages([
            'status' => 'Transisi readiness offboarding tidak valid.',
        ]);
    }
}
