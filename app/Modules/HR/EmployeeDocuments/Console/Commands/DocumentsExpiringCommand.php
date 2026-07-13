<?php

namespace App\Modules\HR\EmployeeDocuments\Console\Commands;

use App\Modules\HR\EmployeeDocuments\Services\EmployeeDocumentExpiryService;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Illuminate\Console\Command;

class DocumentsExpiringCommand extends Command
{
    protected $signature = 'hr:documents-expiring
        {--date= : Window start date in YYYY-MM-DD; defaults to today}
        {--within=30 : Inclusive number of days after the start date (0-3650)}';

    protected $description = 'List employee document metadata expiring in a deterministic, read-only date window';

    public function handle(EmployeeDocumentExpiryService $expiry): int
    {
        $dateInput = (string) ($this->option('date') ?: now()->toDateString());
        $withinInput = (string) $this->option('within');

        if (! $this->validDate($dateInput)) {
            $this->error('The --date option must be a valid date in YYYY-MM-DD format.');

            return self::FAILURE;
        }
        if (! preg_match('/^\d+$/', $withinInput) || (int) $withinInput > 3650) {
            $this->error('The --within option must be an integer from 0 through 3650.');

            return self::FAILURE;
        }

        $date = CarbonImmutable::createFromFormat('!Y-m-d', $dateInput);
        $within = (int) $withinInput;
        $through = $date->addDays($within);
        $documents = $expiry->expiring($date, $within);

        if ($documents->isEmpty()) {
            $this->info("No documents expiring from {$dateInput} through {$through->toDateString()}.");

            return self::SUCCESS;
        }

        $this->table(
            ['Metadata ID', 'Employee', 'Document type', 'Expiry date'],
            $documents->map(fn ($document) => [
                $document->id,
                $document->employee->display_name,
                $document->documentType->name,
                $document->expires_at->toDateString(),
            ])->all(),
        );
        $this->info("{$documents->count()} document(s) expiring from {$dateInput} through {$through->toDateString()}.");

        return self::SUCCESS;
    }

    private function validDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed !== false && $parsed->format('Y-m-d') === $date;
    }
}
