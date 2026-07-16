<?php

namespace App\Modules\HR\Offboardings\Services;

use App\Models\User;
use App\Modules\Console\AuditLogs\Services\AuditLogService;
use App\Modules\HR\Offboardings\DTO\OffboardingTaskAssignmentData;
use App\Modules\HR\Offboardings\DTO\OffboardingTaskCompletionData;
use App\Modules\HR\Offboardings\DTO\OffboardingTaskReasonData;
use App\Modules\HR\Offboardings\Enums\OffboardingStatus;
use App\Modules\HR\Offboardings\Enums\OffboardingTaskStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTask;
use App\Modules\HR\Offboardings\Transactions\OffboardingTransaction;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class OffboardingTaskService
{
    public function __construct(
        private readonly OffboardingTransaction $transaction,
        private readonly AuditLogService $audit,
    ) {}

    public function assign(
        Offboarding $offboarding,
        OffboardingTask $task,
        OffboardingTaskAssignmentData $data,
    ): OffboardingTask {
        return $this->transaction->run(function () use ($offboarding, $task, $data) {
            [$lockedOffboarding, $lockedTask] = $this->lockAggregate($offboarding, $task);
            $this->ensureOffboardingAllowsAssignment($lockedOffboarding);

            if ($lockedTask->status->isTerminal()) {
                $this->invalidTaskTransition();
            }

            if ($data->assigneeUserId !== null
                && ! User::query()->lockForUpdate()->whereKey($data->assigneeUserId)->exists()) {
                throw ValidationException::withMessages([
                    'assignee_user_id' => 'Assignee Console User aktif tidak tersedia.',
                ]);
            }

            if ($lockedTask->assignee_user_id === $data->assigneeUserId) {
                return $lockedTask;
            }

            $oldAssignee = $lockedTask->assignee_user_id;
            $lockedTask->update(['assignee_user_id' => $data->assigneeUserId]);
            $this->audit->record(
                module: 'hr.offboardings',
                event: 'OffboardingTask.assigned',
                auditable: $lockedTask,
                description: "Updated assignee for offboarding task {$lockedTask->id}",
                oldValues: ['assignee_user_id' => $oldAssignee],
                newValues: ['assignee_user_id' => $data->assigneeUserId],
            );

            return $lockedTask->refresh();
        });
    }

    public function start(Offboarding $offboarding, OffboardingTask $task): OffboardingTask
    {
        return $this->transaction->run(function () use ($offboarding, $task) {
            [$lockedOffboarding, $lockedTask] = $this->lockAggregate($offboarding, $task);
            $this->ensureOffboardingInProgress($lockedOffboarding);

            if ($lockedTask->status === OffboardingTaskStatus::InProgress) {
                return $lockedTask;
            }

            if ($lockedTask->status !== OffboardingTaskStatus::Pending) {
                $this->invalidTaskTransition();
            }

            $lockedTask->update(['status' => OffboardingTaskStatus::InProgress]);
            $this->audit->record(
                module: 'hr.offboardings',
                event: 'OffboardingTask.started',
                auditable: $lockedTask,
                description: "Started offboarding task {$lockedTask->id}",
                oldValues: ['status' => OffboardingTaskStatus::Pending->value],
                newValues: ['status' => OffboardingTaskStatus::InProgress->value],
            );

            return $lockedTask->refresh();
        });
    }

    public function complete(
        Offboarding $offboarding,
        OffboardingTask $task,
        OffboardingTaskCompletionData $data,
    ): OffboardingTask {
        return $this->transaction->run(function () use ($offboarding, $task, $data) {
            [$lockedOffboarding, $lockedTask] = $this->lockAggregate($offboarding, $task);
            $this->ensureOffboardingInProgress($lockedOffboarding);

            if ($lockedTask->status === OffboardingTaskStatus::Completed) {
                return $lockedTask;
            }

            if ($lockedTask->status !== OffboardingTaskStatus::InProgress) {
                $this->invalidTaskTransition();
            }

            if (! User::query()->lockForUpdate()->whereKey($data->actorUserId)->exists()) {
                $this->invalidTaskTransition();
            }

            $lockedTask->update([
                'status' => OffboardingTaskStatus::Completed,
                'completed_by_user_id' => $data->actorUserId,
                'completed_at' => now(),
                'completion_note' => $data->note,
            ]);
            $this->audit->record(
                module: 'hr.offboardings',
                event: 'OffboardingTask.completed',
                auditable: $lockedTask,
                description: "Completed offboarding task {$lockedTask->id}",
                oldValues: ['status' => OffboardingTaskStatus::InProgress->value],
                newValues: [
                    'status' => OffboardingTaskStatus::Completed->value,
                    'completed_by_user_id' => $data->actorUserId,
                ],
            );

            return $lockedTask->refresh();
        });
    }

    public function skip(
        Offboarding $offboarding,
        OffboardingTask $task,
        OffboardingTaskReasonData $data,
    ): OffboardingTask {
        return $this->transaction->run(function () use ($offboarding, $task, $data) {
            [$lockedOffboarding, $lockedTask] = $this->lockAggregate($offboarding, $task);
            $this->ensureOffboardingInProgress($lockedOffboarding);

            if (! in_array(
                $lockedTask->status,
                [OffboardingTaskStatus::Pending, OffboardingTaskStatus::InProgress],
                true,
            )) {
                $this->invalidTaskTransition();
            }

            $actor = User::query()->lockForUpdate()->findOrFail($data->actorUserId);
            if ($lockedTask->required
                && ! $actor->hasAnyPermission(['offboardings.task-skip-required', 'offboardings.manage'])) {
                throw new AuthorizationException('Required task skip membutuhkan permission khusus.');
            }

            $oldStatus = $lockedTask->status;
            $lockedTask->update([
                'status' => OffboardingTaskStatus::Skipped,
                'skipped_by_user_id' => $actor->id,
                'skipped_at' => now(),
                'skip_reason' => $data->reason,
            ]);
            $this->audit->record(
                module: 'hr.offboardings',
                event: 'OffboardingTask.skipped',
                auditable: $lockedTask,
                description: "Skipped offboarding task {$lockedTask->id}",
                oldValues: ['status' => $oldStatus->value],
                newValues: [
                    'status' => OffboardingTaskStatus::Skipped->value,
                    'skipped_by_user_id' => $actor->id,
                    'reason' => $data->reason,
                ],
            );

            return $lockedTask->refresh();
        });
    }

    public function reopen(
        Offboarding $offboarding,
        OffboardingTask $task,
        OffboardingTaskReasonData $data,
    ): OffboardingTask {
        return $this->transaction->run(function () use ($offboarding, $task, $data) {
            [$lockedOffboarding, $lockedTask] = $this->lockAggregate($offboarding, $task);
            $this->ensureOffboardingAllowsReopen($lockedOffboarding);

            if (! $lockedTask->status->isTerminal()) {
                $this->invalidTaskTransition();
            }

            if (! User::query()->lockForUpdate()->whereKey($data->actorUserId)->exists()) {
                $this->invalidTaskTransition();
            }

            $oldStatus = $lockedTask->status;
            $lockedTask->update([
                'status' => OffboardingTaskStatus::Pending,
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
                module: 'hr.offboardings',
                event: 'OffboardingTask.reopened',
                auditable: $lockedTask,
                description: "Reopened offboarding task {$lockedTask->id}",
                oldValues: ['status' => $oldStatus->value],
                newValues: [
                    'status' => OffboardingTaskStatus::Pending->value,
                    'reopened_by_user_id' => $data->actorUserId,
                    'reason' => $data->reason,
                ],
            );

            if ($lockedOffboarding->status === OffboardingStatus::ReadyForExit) {
                $lockedOffboarding->update(['status' => OffboardingStatus::InProgress]);
                $this->audit->record(
                    module: 'hr.offboardings',
                    event: 'Offboarding.readiness_revoked',
                    auditable: $lockedOffboarding,
                    description: "Revoked readiness for offboarding {$lockedOffboarding->id}",
                    oldValues: ['status' => OffboardingStatus::ReadyForExit->value],
                    newValues: ['status' => OffboardingStatus::InProgress->value],
                );
            }

            return $lockedTask->refresh();
        });
    }

    /** @return array{Offboarding, OffboardingTask} */
    private function lockAggregate(Offboarding $offboarding, OffboardingTask $task): array
    {
        $lockedOffboarding = Offboarding::query()
            ->withTrashed()
            ->lockForUpdate()
            ->findOrFail($offboarding->id);
        $lockedTask = OffboardingTask::query()
            ->where('offboarding_id', $lockedOffboarding->id)
            ->lockForUpdate()
            ->findOrFail($task->id);

        return [$lockedOffboarding, $lockedTask];
    }

    private function ensureOffboardingAllowsAssignment(Offboarding $offboarding): void
    {
        if ($offboarding->trashed()
            || ! in_array($offboarding->status, [OffboardingStatus::Draft, OffboardingStatus::InProgress], true)) {
            $this->invalidOffboardingState();
        }
    }

    private function ensureOffboardingInProgress(Offboarding $offboarding): void
    {
        if ($offboarding->trashed() || $offboarding->status !== OffboardingStatus::InProgress) {
            $this->invalidOffboardingState();
        }
    }

    private function ensureOffboardingAllowsReopen(Offboarding $offboarding): void
    {
        if ($offboarding->trashed()
            || ! in_array(
                $offboarding->status,
                [OffboardingStatus::InProgress, OffboardingStatus::ReadyForExit],
                true,
            )) {
            $this->invalidOffboardingState();
        }
    }

    private function invalidOffboardingState(): never
    {
        throw ValidationException::withMessages([
            'status' => 'Task tidak dapat diubah pada status offboarding saat ini.',
        ]);
    }

    private function invalidTaskTransition(): never
    {
        throw ValidationException::withMessages(['status' => 'Transisi status task tidak valid.']);
    }
}
