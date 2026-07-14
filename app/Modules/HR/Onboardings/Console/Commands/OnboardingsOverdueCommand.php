<?php

namespace App\Modules\HR\Onboardings\Console\Commands;

use App\Modules\HR\Onboardings\Enums\OnboardingStatus;
use App\Modules\HR\Onboardings\Enums\OnboardingTaskStatus;
use App\Modules\HR\Onboardings\Models\Onboarding;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class OnboardingsOverdueCommand extends Command
{
    protected $signature = 'hr:onboardings:overdue {--date= : Business date in YYYY-MM-DD format}';

    protected $description = 'List active onboarding cases with incomplete tasks overdue before an explicit business date';

    public function handle(): int
    {
        $value = $this->option('date');
        if (! is_string($value) || ! $this->validDate($value)) {
            $this->error('Option --date wajib memakai format YYYY-MM-DD yang valid.');

            return self::FAILURE;
        }

        $rows = Onboarding::query()
            ->with('employee:id,employee_number,display_name')
            ->whereIn('status', [OnboardingStatus::Draft->value, OnboardingStatus::InProgress->value])
            ->withCount(['tasks as overdue_tasks_count' => fn ($query) => $query
                ->whereIn('status', [OnboardingTaskStatus::Pending->value, OnboardingTaskStatus::InProgress->value])
                ->whereDate('due_date', '<', $value)])
            ->whereHas('tasks', fn ($query) => $query
                ->whereIn('status', [OnboardingTaskStatus::Pending->value, OnboardingTaskStatus::InProgress->value])
                ->whereDate('due_date', '<', $value))
            ->orderBy('id')
            ->get();

        $this->table(['ID', 'Employee', 'Status', 'Start date', 'Overdue tasks'], $rows->map(fn (Onboarding $onboarding) => [
            $onboarding->id,
            $onboarding->employee?->employee_number ?? 'N/A',
            $onboarding->status->value,
            $onboarding->start_date?->format('Y-m-d'),
            $onboarding->overdue_tasks_count,
        ])->all());

        return self::SUCCESS;
    }

    private function validDate(string $value): bool
    {
        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', $value)->format('Y-m-d') === $value;
        } catch (\Throwable) {
            return false;
        }
    }
}
