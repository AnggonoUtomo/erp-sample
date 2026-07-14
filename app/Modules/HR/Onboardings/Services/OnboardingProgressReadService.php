<?php

namespace App\Modules\HR\Onboardings\Services;

use App\Modules\HR\Onboardings\Enums\OnboardingTaskStatus;
use App\Modules\HR\Onboardings\Models\Onboarding;
use App\Modules\HR\Onboardings\Models\OnboardingTask;
use Carbon\CarbonImmutable;

class OnboardingProgressReadService
{
    /** @return array<string, mixed> */
    public function detail(Onboarding $onboarding, string $businessDate): array
    {
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $businessDate);
        $onboarding->load([
            'employee:id,employee_number,display_name',
            'contract:id,contract_number,start_date,end_date,status',
            'template:id,code,name',
            'owner:id,name',
            'tasks.assignee:id,name',
            'tasks.completedBy:id,name',
            'tasks.skippedBy:id,name',
            'tasks.reopenedBy:id,name',
        ]);

        $tasks = $onboarding->tasks;
        $terminal = $tasks->filter(fn (OnboardingTask $task) => $task->status->isTerminal());
        $incomplete = $tasks->reject(fn (OnboardingTask $task) => $task->status->isTerminal());
        $total = $tasks->count();

        return [
            'id' => $onboarding->id,
            'employee' => $onboarding->employee?->only(['id', 'employee_number', 'display_name']),
            'contract' => $onboarding->contract ? [
                'id' => $onboarding->contract->id,
                'contract_number' => $onboarding->contract->contract_number,
                'start_date' => $onboarding->contract->start_date?->format('Y-m-d'),
                'end_date' => $onboarding->contract->end_date?->format('Y-m-d'),
                'status' => $onboarding->contract->status->value,
            ] : null,
            'template' => $onboarding->template?->only(['id', 'code', 'name']),
            'owner' => $onboarding->owner?->only(['id', 'name']),
            'start_date' => $onboarding->start_date?->format('Y-m-d'),
            'status' => $onboarding->status->value,
            'archived' => $onboarding->trashed(),
            'created_at' => $onboarding->created_at?->toIso8601String(),
            'progress' => [
                'total' => $total,
                'terminal' => $terminal->count(),
                'completed' => $tasks->where('status', OnboardingTaskStatus::Completed)->count(),
                'required_incomplete' => $incomplete->where('required', true)->count(),
                'optional_incomplete' => $incomplete->where('required', false)->count(),
                'overdue' => $incomplete->filter(fn (OnboardingTask $task) => $task->due_date->lt($date))->count(),
                'percentage' => $total === 0 ? 0 : (int) floor(($terminal->count() / $total) * 100),
            ],
            'tasks' => $tasks->map(fn (OnboardingTask $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'category' => $task->category,
                'required' => $task->required,
                'due_date' => $task->due_date->format('Y-m-d'),
                'sort_order' => $task->sort_order,
                'status' => $task->status->value,
                'overdue' => ! $task->status->isTerminal() && $task->due_date->lt($date),
                'assignee' => $task->assignee?->only(['id', 'name']),
                'completed_by' => $task->completedBy?->only(['id', 'name']),
                'completed_at' => $task->completed_at?->toIso8601String(),
                'completion_note' => $task->completion_note,
                'skipped_by' => $task->skippedBy?->only(['id', 'name']),
                'skipped_at' => $task->skipped_at?->toIso8601String(),
                'skip_reason' => $task->skip_reason,
                'reopened_by' => $task->reopenedBy?->only(['id', 'name']),
                'reopened_at' => $task->reopened_at?->toIso8601String(),
                'reopen_reason' => $task->reopen_reason,
            ])->values(),
        ];
    }
}
