<?php

namespace App\Modules\HR\IntegrationContracts\Console\Commands;

use App\Modules\HR\IntegrationContracts\Support\HRIntegrationContractRegistry;
use Illuminate\Console\Command;

class DescribeHRIntegrationContractsCommand extends Command
{
    protected $signature = 'hr:integration-contracts:describe {--json : Emit machine-readable JSON}';

    protected $description = 'Describe HR integration snapshot and event contracts without reading business data';

    public function handle(HRIntegrationContractRegistry $contracts): int
    {
        $rows = [
            ...collect($contracts->snapshotContracts())
                ->map(fn (string $contract): array => ['Snapshot', $contract, 1])
                ->all(),
            ...collect($contracts->eventContracts())
                ->map(fn (string $contract): array => ['Event', $contract, 1])
                ->all(),
        ];

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode([
                'title' => 'HR Integration Contracts v1',
                'snapshots' => $contracts->snapshotContracts(),
                'events' => $contracts->eventContracts(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info('HR Integration Contracts v1');
        $this->table(['Type', 'Contract', 'Version'], $rows);

        return self::SUCCESS;
    }
}
