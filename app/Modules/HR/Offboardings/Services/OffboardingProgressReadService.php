<?php

namespace App\Modules\HR\Offboardings\Services;

use App\Modules\HR\Offboardings\Enums\OffboardingTaskStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use App\Modules\HR\Offboardings\Models\OffboardingTask;
use BackedEnum;
use Carbon\CarbonImmutable;

class OffboardingProgressReadService
{
    /** @return array<string, mixed> */
    public function detail(Offboarding $offboarding, string $businessDate): array
    {
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $businessDate);
        $offboarding->load([
            'employee:id,employee_number,display_name',
            'contract:id,contract_number,start_date,end_date,status',
            'template:id,code,name',
            'targetEmploymentStatus:id,code,name',
            'owner:id,name',
            'tasks.assignee:id,name',
            'tasks.completedBy:id,name',
        ]);

        $tasks = $offboarding->tasks;
        $terminal = $tasks->filter(fn (OffboardingTask $task) => $task->status->isTerminal());
        $incomplete = $tasks->reject(fn (OffboardingTask $task) => $task->status->isTerminal());
        $total = $tasks->count();

        return [
            'id' => $offboarding->id,
            'employee' => $offboarding->employee?->only(['id', 'employee_number', 'display_name']),
            'contract' => $offboarding->contract ? [
                'id' => $offboarding->contract->id,
                'contract_number' => $offboarding->contract->contract_number,
                'start_date' => $offboarding->contract->start_date?->format('Y-m-d'),
                'end_date' => $offboarding->contract->end_date?->format('Y-m-d'),
                'status' => $offboarding->contract->status instanceof BackedEnum
                    ? $offboarding->contract->status->value
                    : (string) $offboarding->contract->status,
            ] : null,
            'template' => $offboarding->template?->only(['id', 'code', 'name']),
            'target_status' => $offboarding->targetEmploymentStatus?->only(['id', 'code', 'name']),
            'owner' => $offboarding->owner?->only(['id', 'name']),
            'exit_date' => $offboarding->exit_date?->format('Y-m-d'),
            'exit_type' => $offboarding->exit_type->value,
            'exit_reason' => $offboarding->exit_reason,
            'notes' => $offboarding->notes,
            'status' => $offboarding->status->value,
            'archived' => $offboarding->trashed(),
            'created_at' => $offboarding->created_at?->toIso8601String(),
            'progress' => [
                'total' => $total,
                'terminal' => $terminal->count(),
                'completed' => $tasks
                    ->filter(fn (OffboardingTask $task) => $task->status === OffboardingTaskStatus::Completed)
                    ->count(),
                'skipped' => $tasks
                    ->filter(fn (OffboardingTask $task) => $task->status === OffboardingTaskStatus::Skipped)
                    ->count(),
                'required_incomplete' => $incomplete->where('required', true)->count(),
                'optional_incomplete' => $incomplete->where('required', false)->count(),
                'overdue' => $incomplete
                    ->filter(fn (OffboardingTask $task) => $task->due_date->lt($date))
                    ->count(),
                'percentage' => $total === 0
                    ? 0
                    : (int) floor(($terminal->count() / $total) * 100),
            ],
            'tasks' => $tasks->map(fn (OffboardingTask $task) => [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'category' => $task->category,
                'required' => $task->required,
                'due_offset_days' => $task->due_offset_days,
                'due_date' => $task->due_date->format('Y-m-d'),
                'default_assignee_role' => $task->default_assignee_role,
                'sort_order' => $task->sort_order,
                'status' => $task->status->value,
                'overdue' => ! $task->status->isTerminal() && $task->due_date->lt($date),
                'assignee' => $task->assignee?->only(['id', 'name']),
                'completed_by' => $task->completedBy?->only(['id', 'name']),
                'completed_at' => $task->completed_at?->toIso8601String(),
                'completion_note' => $task->completion_note,
            ])->values(),
        ];
    }
}
