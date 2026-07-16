<?php

namespace App\Modules\HR\EmployeeMovements\Console\Commands;

use App\Modules\HR\EmployeeMovements\Services\EmployeeMovementsService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class ApplyDueEmployeeMovementsCommand extends Command
{
    protected $signature = 'hr:employee-movements:apply-due {--date= : Business date in YYYY-MM-DD format} {--dry-run : List due movements without mutating data}';

    protected $description = 'Apply DRAFT employee movements whose effective date is due on or before an explicit business date';

    public function handle(EmployeeMovementsService $movements): int
    {
        $date = $this->option('date');
        if (! is_string($date) || ! $this->validDate($date)) {
            $this->error('Option --date wajib memakai format YYYY-MM-DD yang valid.');

            return self::FAILURE;
        }

        $result = $movements->applyDue($date, (bool) $this->option('dry-run'));
        $this->info("Due movements: {$result['due']}; applied: {$result['applied']}; failed: {$result['failed']}.");
        if (! empty($result['errors'])) {
            foreach ($result['errors'] as $error) {
                $this->warn($error);
            }
        }

        return $result['failed'] > 0 ? self::FAILURE : self::SUCCESS;
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
