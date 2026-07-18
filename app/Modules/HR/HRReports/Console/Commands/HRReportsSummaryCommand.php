<?php

namespace App\Modules\HR\HRReports\Console\Commands;

use App\Modules\HR\HRReports\DTO\HeadcountReportFilters;
use App\Modules\HR\HRReports\Services\HeadcountReportService;
use DateTimeImmutable;
use Illuminate\Console\Command;

class HRReportsSummaryCommand extends Command
{
    protected $signature = 'hr:reports:summary
        {--date= : Report date in YYYY-MM-DD; defaults to today}';

    protected $description = 'Show deterministic read-only HR headcount summary reports';

    public function handle(HeadcountReportService $headcount): int
    {
        $dateInput = (string) ($this->option('date') ?: now()->toDateString());

        if (! $this->validDate($dateInput)) {
            $this->error('The --date option must be a valid date in YYYY-MM-DD format.');

            return self::FAILURE;
        }

        $filters = new HeadcountReportFilters(asOf: $dateInput);
        $rows = collect([
            'Departement' => $headcount->byDepartement($filters),
            'Work Location' => $headcount->byWorkLocation($filters),
            'Employment Status' => $headcount->byEmploymentStatus($filters),
        ])->flatMap(fn ($report, string $label) => collect($report->rows)->map(fn (array $row) => [
            $label,
            $row['name'],
            $row['employeeCount'],
        ]));

        if ($rows->isEmpty()) {
            $this->info("No headcount data for {$dateInput}.");

            return self::SUCCESS;
        }

        $this->table(['Report', 'Group', 'Employee count'], $rows->all());
        $this->info("HR report summary for {$dateInput}.");

        return self::SUCCESS;
    }

    private function validDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
