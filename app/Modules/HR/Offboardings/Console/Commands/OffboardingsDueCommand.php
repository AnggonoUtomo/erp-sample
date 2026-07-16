<?php

namespace App\Modules\HR\Offboardings\Console\Commands;

use App\Modules\HR\Offboardings\Enums\OffboardingStatus;
use App\Modules\HR\Offboardings\Enums\OffboardingTaskStatus;
use App\Modules\HR\Offboardings\Models\Offboarding;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class OffboardingsDueCommand extends Command
{
    protected $signature = 'hr:offboardings:due {--date= : Business date in YYYY-MM-DD format} {--within=30 : Due window in days, 0..365}';

    protected $description = 'List active offboarding cases with incomplete tasks due within an explicit business-date window';

    public function handle(): int
    {
        $date = $this->option('date');
        if (! is_string($date) || ! $this->validDate($date)) {
            $this->error('Option --date wajib memakai format YYYY-MM-DD yang valid.');

            return self::FAILURE;
        }

        $within = $this->resolveWithin();
        if ($within === null) {
            $this->error('Option --within wajib berupa angka 0 sampai 365.');

            return self::FAILURE;
        }

        $until = CarbonImmutable::createFromFormat('!Y-m-d', $date)->addDays($within)->format('Y-m-d');
        $incomplete = [OffboardingTaskStatus::Pending->value, OffboardingTaskStatus::InProgress->value];
        $activeStatuses = [OffboardingStatus::Draft->value, OffboardingStatus::InProgress->value, OffboardingStatus::ReadyForExit->value];
        $rows = Offboarding::query()
            ->with('employee:id,employee_number,display_name')
            ->whereIn('status', $activeStatuses)
            ->withCount(['tasks as due_tasks_count' => fn ($query) => $query
                ->whereIn('status', $incomplete)
                ->whereDate('due_date', '<=', $until)])
            ->whereHas('tasks', fn ($query) => $query
                ->whereIn('status', $incomplete)
                ->whereDate('due_date', '<=', $until))
            ->orderBy('id')
            ->get();

        $this->table(['ID', 'Employee', 'Status', 'Exit date', 'Due tasks'], $rows->map(fn (Offboarding $offboarding) => [
            $offboarding->id,
            $offboarding->employee?->employee_number ?? 'N/A',
            $offboarding->status->value,
            $offboarding->exit_date?->format('Y-m-d'),
            $offboarding->due_tasks_count,
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

    private function resolveWithin(): ?int
    {
        $value = $this->option('within');
        if (! is_numeric($value)) {
            return null;
        }

        $within = (int) $value;

        return $within >= 0 && $within <= 365 ? $within : null;
    }
}
