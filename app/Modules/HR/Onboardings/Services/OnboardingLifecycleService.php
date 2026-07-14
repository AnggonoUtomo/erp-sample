<?php

namespace App\Modules\HR\Onboardings\Services;

use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Onboardings\DTO\OnboardingTaskReasonData;
use App\Modules\HR\Onboardings\Enums\OnboardingStatus;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTask;
use App\Modules\HR\Onboardings\Transactions\OnboardingTransaction;
use Illuminate\Validation\ValidationException;

class OnboardingLifecycleService
{
    public function __construct(private readonly OnboardingTransaction $transaction, private readonly AuditLogService $audit) {}

    public function complete(Onboarding $onboarding, int $actorUserId): Onboarding
    {
        return $this->transaction->run(function () use ($onboarding, $actorUserId) {
            $locked = $this->lockOnboarding($onboarding);
            if ($locked->trashed() || $locked->status !== OnboardingStatus::InProgress) {
                $this->invalidTransition();
            }

            $tasks = OnboardingTask::query()->where('onboarding_id', $locked->id)->lockForUpdate()->get();
            if ($tasks->contains(fn (OnboardingTask $task) => $task->required && ! $task->status->isTerminal())) {
                throw ValidationException::withMessages(['status' => 'Semua task wajib harus selesai atau di-skip secara sah sebelum onboarding diselesaikan.']);
            }

            $locked->update([
                'status' => OnboardingStatus::Completed,
                'completed_by_user_id' => $actorUserId,
                'completed_at' => now(),
                'active_identity_key' => null,
            ]);
            $this->audit->record(
                module: 'hr.onboardings', event: 'Onboarding.completed', auditable: $locked,
                description: "Completed onboarding {$locked->id}",
                oldValues: ['status' => OnboardingStatus::InProgress->value],
                newValues: ['status' => OnboardingStatus::Completed->value, 'completed_by_user_id' => $actorUserId],
            );

            return $locked->refresh();
        });
    }

    public function cancel(Onboarding $onboarding, OnboardingTaskReasonData $data): Onboarding
    {
        return $this->transaction->run(function () use ($onboarding, $data) {
            $locked = $this->lockOnboarding($onboarding);
            if ($locked->trashed() || ! in_array($locked->status, [OnboardingStatus::Draft, OnboardingStatus::InProgress], true)) {
                $this->invalidTransition();
            }

            $oldStatus = $locked->status;
            $locked->update([
                'status' => OnboardingStatus::Cancelled,
                'cancelled_by_user_id' => $data->actorUserId,
                'cancelled_at' => now(),
                'cancel_reason' => $data->reason,
                'active_identity_key' => null,
            ]);
            $this->audit->record(
                module: 'hr.onboardings', event: 'Onboarding.cancelled', auditable: $locked,
                description: "Cancelled onboarding {$locked->id}",
                oldValues: ['status' => $oldStatus->value],
                newValues: ['status' => OnboardingStatus::Cancelled->value, 'cancelled_by_user_id' => $data->actorUserId, 'reason' => $data->reason],
            );

            return $locked->refresh();
        });
    }

    private function lockOnboarding(Onboarding $onboarding): Onboarding
    {
        return Onboarding::query()->withTrashed()->lockForUpdate()->findOrFail($onboarding->id);
    }

    private function invalidTransition(): never
    {
        throw ValidationException::withMessages(['status' => 'Transisi lifecycle onboarding tidak valid.']);
    }
}
