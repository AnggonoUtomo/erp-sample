<?php

namespace App\Modules\HR\IntegrationContracts\Console\Commands;

use App\Modules\HR\IntegrationContracts\Contracts\EmployeeAssignmentSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeContractSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeDocumentComplianceSnapshotProvider;
use App\Modules\HR\IntegrationContracts\Contracts\EmployeeSnapshotProvider;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Throwable;

class SampleHRIntegrationContractsCommand extends Command
{
    protected $signature = 'hr:integration-contracts:sample
        {employeeId : Employee id to sample}
        {--date= : Business date in YYYY-MM-DD format}
        {--warning-days=30 : Document expiry warning window from 0 to 365 days}';

    protected $description = 'Print a safe HR integration sample payload for one employee without mutation';

    public function handle(
        EmployeeSnapshotProvider $employees,
        EmployeeAssignmentSnapshotProvider $assignments,
        EmployeeContractSnapshotProvider $contracts,
        EmployeeDocumentComplianceSnapshotProvider $documents,
    ): int {
        $employeeId = filter_var($this->argument('employeeId'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if (! is_int($employeeId)) {
            $this->error('Argument employeeId wajib berupa integer positif.');

            return self::FAILURE;
        }

        $date = $this->option('date');
        if (! is_string($date) || ! $this->validDate($date)) {
            $this->error('Option --date wajib memakai format YYYY-MM-DD yang valid.');

            return self::FAILURE;
        }

        $warningDays = filter_var($this->option('warning-days'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 0, 'max_range' => 365],
        ]);

        if (! is_int($warningDays)) {
            $this->error('Option --warning-days wajib berupa integer 0 sampai 365.');

            return self::FAILURE;
        }

        $employee = $employees->forEmployee($employeeId);
        if ($employee === null) {
            $this->error('Employee not found for sample payload.');

            return self::FAILURE;
        }

        $payload = [
            'employee' => $employee->toArray(),
            'assignment' => $assignments->forEmployee($employeeId, $date)?->toArray(),
            'contract' => $contracts->forEmployee($employeeId, $date)?->toArray(),
            'documentCompliance' => $documents->forEmployee($employeeId, $date, $warningDays)?->toArray(),
        ];

        $this->line('Sample sections: employee, assignment, contract, documentCompliance');
        $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return self::SUCCESS;
    }

    private function validDate(string $value): bool
    {
        try {
            return CarbonImmutable::createFromFormat('!Y-m-d', $value)->format('Y-m-d') === $value;
        } catch (Throwable) {
            return false;
        }
    }
}
