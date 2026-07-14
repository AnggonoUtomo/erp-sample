<?php

namespace App\Modules\HR\Onboardings\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Onboardings\DTO\OnboardingTaskAssignmentData;
use App\Modules\HR\Onboardings\DTO\OnboardingTaskCompletionData;
use App\Modules\HR\Onboardings\DTO\OnboardingTaskReasonData;
use App\Modules\HR\Onboardings\Enums\OnboardingStatus;
use App\Modules\HR\Onboardings\Enums\OnboardingTaskStatus;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTask;
use App\Modules\HR\Onboardings\Transactions\OnboardingTransaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class OnboardingTaskService
{
    public function __construct(private readonly OnboardingTransaction $transaction, private readonly AuditLogService $audit) {}

    public function assign(Onboarding $onboarding, OnboardingTask $task, OnboardingTaskAssignmentData $data): OnboardingTask
    {
        return $this->transaction->run(function () use ($onboarding, $task, $data) {
            [$lockedOnboarding, $lockedTask] = $this->lockAggregate($onboarding, $task);
            $this->ensureOnboardingAllowsAssignment($lockedOnboarding);

            if ($lockedTask->status->isTerminal()) {
                $this->invalidTaskTransition();
            }

            if ($lockedTask->assignee_user_id === $data->assigneeUserId) {
                return $lockedTask;
            }

            $oldAssignee = $lockedTask->assignee_user_id;
            $lockedTask->update(['assignee_user_id' => $data->assigneeUserId]);
            $this->audit->record(
                module: 'hr.onboardings',
                event: 'OnboardingTask.assigned',
                auditable: $lockedTask,
                description: "Updated assignee for onboarding task {$lockedTask->id}",
                oldValues: ['assignee_user_id' => $oldAssignee],
                newValues: ['assignee_user_id' => $data->assigneeUserId],
            );

            return $lockedTask->refresh();
        });
    }

    public function start(Onboarding $onboarding, OnboardingTask $task): OnboardingTask
    {
        return $this->transaction->run(function () use ($onboarding, $task) {
            [$lockedOnboarding, $lockedTask] = $this->lockAggregate($onboarding, $task);
            $this->ensureOnboardingInProgress($lockedOnboarding);

            if ($lockedTask->status === OnboardingTaskStatus::InProgress) {
                return $lockedTask;
            }

            if ($lockedTask->status !== OnboardingTaskStatus::Pending) {
                $this->invalidTaskTransition();
            }

            $lockedTask->update(['status' => OnboardingTaskStatus::InProgress]);
            $this->audit->record(
                module: 'hr.onboardings',
                event: 'OnboardingTask.started',
                auditable: $lockedTask,
                description: "Started onboarding task {$lockedTask->id}",
                oldValues: ['status' => OnboardingTaskStatus::Pending->value],
                newValues: ['status' => OnboardingTaskStatus::InProgress->value],
            );

            return $lockedTask->refresh();
        });
    }

    public function complete(Onboarding $onboarding, OnboardingTask $task, OnboardingTaskCompletionData $data): OnboardingTask
    {
        return $this->transaction->run(function () use ($onboarding, $task, $data) {
            [$lockedOnboarding, $lockedTask] = $this->lockAggregate($onboarding, $task);
            $this->ensureOnboardingInProgress($lockedOnboarding);

            if ($lockedTask->status === OnboardingTaskStatus::Completed) {
                return $lockedTask;
            }

            if ($lockedTask->status !== OnboardingTaskStatus::InProgress) {
                $this->invalidTaskTransition();
            }

            $lockedTask->update([
                'status' => OnboardingTaskStatus::Completed,
                'completed_by_user_id' => $data->actorUserId,
                'completed_at' => now(),
                'completion_note' => $data->note,
            ]);
            $this->audit->record(
                module: 'hr.onboardings',
                event: 'OnboardingTask.completed',
                auditable: $lockedTask,
                description: "Completed onboarding task {$lockedTask->id}",
                oldValues: ['status' => OnboardingTaskStatus::InProgress->value],
                newValues: ['status' => OnboardingTaskStatus::Completed->value, 'completed_by_user_id' => $data->actorUserId],
            );

            return $lockedTask->refresh();
        });
    }

    public function skip(Onboarding $onboarding, OnboardingTask $task, OnboardingTaskReasonData $data): OnboardingTask
    {
        return $this->transaction->run(function () use ($onboarding, $task, $data) {
            [$lockedOnboarding, $lockedTask] = $this->lockAggregate($onboarding, $task);
            $this->ensureOnboardingInProgress($lockedOnboarding);

            if (! in_array($lockedTask->status, [OnboardingTaskStatus::Pending, OnboardingTaskStatus::InProgress], true)) {
                $this->invalidTaskTransition();
            }

            $actor = User::query()->findOrFail($data->actorUserId);
            if ($lockedTask->required && ! $actor->hasAnyPermission(['onboardings.task-skip-required', 'onboardings.manage'])) {
                throw new AuthorizationException('Required task skip membutuhkan permission khusus.');
            }

            $oldStatus = $lockedTask->status;
            $lockedTask->update([
                'status' => OnboardingTaskStatus::Skipped,
                'skipped_by_user_id' => $actor->id,
                'skipped_at' => now(),
                'skip_reason' => $data->reason,
            ]);
            $this->audit->record(
                module: 'hr.onboardings',
                event: 'OnboardingTask.skipped',
                auditable: $lockedTask,
                description: "Skipped onboarding task {$lockedTask->id}",
                oldValues: ['status' => $oldStatus->value],
                newValues: ['status' => OnboardingTaskStatus::Skipped->value, 'skipped_by_user_id' => $actor->id, 'reason' => $data->reason],
            );

            return $lockedTask->refresh();
        });
    }

    public function reopen(Onboarding $onboarding, OnboardingTask $task, OnboardingTaskReasonData $data): OnboardingTask
    {
        return $this->transaction->run(function () use ($onboarding, $task, $data) {
            [$lockedOnboarding, $lockedTask] = $this->lockAggregate($onboarding, $task);
            $this->ensureOnboardingInProgress($lockedOnboarding);

            if (! $lockedTask->status->isTerminal()) {
                $this->invalidTaskTransition();
            }

            $oldStatus = $lockedTask->status;
            $lockedTask->update([
                'status' => OnboardingTaskStatus::Pending,
                'completed_by_user_id' => null,
                'completed_at' => null,
                'completion_note' => null,
                'skipped_by_user_id' => null,
                'skipped_at' => null,
                'skip_reason' => null,
                'reopened_by_user_id' => $data->actorUserId,
                'reopened_at' => now(),
                'reopen_reason' => $data->reason,
            ]);
            $this->audit->record(
                module: 'hr.onboardings',
                event: 'OnboardingTask.reopened',
                auditable: $lockedTask,
                description: "Reopened onboarding task {$lockedTask->id}",
                oldValues: ['status' => $oldStatus->value],
                newValues: ['status' => OnboardingTaskStatus::Pending->value, 'reopened_by_user_id' => $data->actorUserId, 'reason' => $data->reason],
            );

            return $lockedTask->refresh();
        });
    }

    /** @return array{Onboarding, OnboardingTask} */
    private function lockAggregate(Onboarding $onboarding, OnboardingTask $task): array
    {
        $lockedOnboarding = Onboarding::query()->withTrashed()->lockForUpdate()->findOrFail($onboarding->id);
        $lockedTask = OnboardingTask::query()
            ->where('onboarding_id', $lockedOnboarding->id)
            ->lockForUpdate()
            ->findOrFail($task->id);

        return [$lockedOnboarding, $lockedTask];
    }

    private function ensureOnboardingAllowsAssignment(Onboarding $onboarding): void
    {
        if ($onboarding->trashed() || ! in_array($onboarding->status, [OnboardingStatus::Draft, OnboardingStatus::InProgress], true)) {
            $this->invalidOnboardingState();
        }
    }

    private function ensureOnboardingInProgress(Onboarding $onboarding): void
    {
        if ($onboarding->trashed() || $onboarding->status !== OnboardingStatus::InProgress) {
            $this->invalidOnboardingState();
        }
    }

    private function invalidOnboardingState(): never
    {
        throw ValidationException::withMessages(['status' => 'Task tidak dapat diubah pada status onboarding saat ini.']);
    }

    private function invalidTaskTransition(): never
    {
        throw ValidationException::withMessages(['status' => 'Transisi status task tidak valid.']);
    }
}
