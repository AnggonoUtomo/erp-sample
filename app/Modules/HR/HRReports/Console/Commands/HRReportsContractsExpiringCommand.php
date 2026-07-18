<?php

namespace App\Modules\HR\HRReports\Console\Commands;

use App\Modules\HR\HRReports\DTO\ExpiryReportFilters;
use App\Modules\HR\HRReports\Services\ContractExpiryReportService;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Console\Command;

class HRReportsContractsExpiringCommand extends Command
{
    protected $signature = 'hr:reports:contracts-expiring
        {--date= : Window start date in YYYY-MM-DD; defaults to today}
        {--within=30 : Inclusive number of days after the start date (0-3650)}';

    protected $description = 'Show active employee contracts expiring in a deterministic read-only date window';

    public function handle(ContractExpiryReportService $expiry): int
    {
        $dateInput = (string) ($this->option('date') ?: now()->toDateString());
        $withinInput = (string) $this->option('within');

        if (! $this->validDate($dateInput)) {
            $this->error('The --date option must be a valid date in YYYY-MM-DD format.');

            return self::FAILURE;
        }

        if (! $this->validWindow($withinInput)) {
            $this->error('The --within option must be an integer from 0 through 3650.');

            return self::FAILURE;
        }

        $within = (int) $withinInput;
        $through = CarbonImmutable::createFromFormat('!Y-m-d', $dateInput)->addDays($within)->toDateString();
        $report = $expiry->expiring(new ExpiryReportFilters(asOf: $dateInput, withinDays: $within));

        if ($report->total === 0) {
            $this->info("No contract expiry rows from {$dateInput} through {$through}.");

            return self::SUCCESS;
        }

        $this->table(
            ['Employee #', 'Employee', 'Employment type', 'Expiry date', 'State', 'Days remaining'],
            collect($report->rows)->map(fn (array $row) => [
                $row['employeeNumber'],
                $row['employeeName'],
                $row['typeLabel'],
                $row['expiresAt'],
                $row['state'],
                $row['daysRemaining'],
            ])->all(),
        );
        $this->info("{$report->total} contract expiry row(s) from {$dateInput} through {$through}.");

        return self::SUCCESS;
    }

    private function validDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }

    private function validWindow(string $within): bool
    {
        return preg_match('/^\d+$/', $within) === 1 && (int) $within <= 3650;
    }
}
