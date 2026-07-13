<?php

namespace App\Modules\HR\EmployeeContracts\Console\Commands;

use App\Modules\HR\EmployeeContracts\Services\EmployeeContractExpiryService;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Console\Command;

class ContractsExpiringCommand extends Command
{
    protected $signature = 'hr:contracts-expiring
        {--date= : Window start date in YYYY-MM-DD; defaults to today}
        {--within=30 : Inclusive number of days after the start date}';

    protected $description = 'List active employee contracts expiring in a deterministic, read-only date window';

    public function handle(EmployeeContractExpiryService $expiry): int
    {
        $dateInput = (string) ($this->option('date') ?: now()->toDateString());
        $withinInput = (string) $this->option('within');

        if (! $this->validDate($dateInput)) {
            $this->error('The --date option must be a valid date in YYYY-MM-DD format.');

            return self::FAILURE;
        }

        if (! preg_match('/^\d+$/', $withinInput)) {
            $this->error('The --within option must be a non-negative integer.');

            return self::FAILURE;
        }

        $date = CarbonImmutable::createFromFormat('!Y-m-d', $dateInput);
        $within = (int) $withinInput;
        $through = $date->addDays($within);
        $contracts = $expiry->expiring($date, $within);

        if ($contracts->isEmpty()) {
            $this->info("No contracts expiring from {$dateInput} through {$through->toDateString()}.");

            return self::SUCCESS;
        }

        $this->table(
            ['Contract', 'Employee', 'End date'],
            $contracts->map(fn ($contract) => [
                $contract->contract_number,
                $contract->employee->display_name,
                $contract->end_date->toDateString(),
            ])->all(),
        );
        $this->info("{$contracts->count()} contract(s) expiring from {$dateInput} through {$through->toDateString()}.");

        return self::SUCCESS;
    }

    private function validDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
