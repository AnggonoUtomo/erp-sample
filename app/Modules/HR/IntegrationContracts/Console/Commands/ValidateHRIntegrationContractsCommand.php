<?php

namespace App\Modules\HR\IntegrationContracts\Console\Commands;

use App\Modules\HR\IntegrationContracts\Support\ForbiddenIntegrationFieldGuard;
use App\Modules\HR\IntegrationContracts\Support\HRIntegrationContractRegistry;
use App\Modules\HR\IntegrationContracts\Support\HRIntegrationEventRegistry;
use App\Support\Modules\ModuleRegistry;
use Illuminate\Console\Command;
use InvalidArgumentException;

class ValidateHRIntegrationContractsCommand extends Command
{
    protected $signature = 'hr:integration-contracts:validate {--json : Emit machine-readable JSON}';

    protected $description = 'Validate HR integration registry, module manifest, and privacy guard without mutation';

    public function handle(
        HRIntegrationContractRegistry $contracts,
        HRIntegrationEventRegistry $events,
        ForbiddenIntegrationFieldGuard $guard,
    ): int {
        $module = ModuleRegistry::modules()->firstWhere('name', 'IntegrationContracts');
        $errors = [];

        if (! $module) {
            $errors[] = 'IntegrationContracts module manifest is not registered.';
        } else {
            if (($module['integrations']['read_models'] ?? []) !== $contracts->snapshotContracts()) {
                $errors[] = 'Snapshot contracts do not match module manifest read_models.';
            }

            if (($module['integrations']['events'] ?? []) !== $contracts->eventContracts()) {
                $errors[] = 'Event contracts do not match module manifest events.';
            }

            if (array_keys($events->events()) !== $contracts->eventContracts()) {
                $errors[] = 'Event registry keys do not match contract registry events.';
            }

            if (($module['events'] ?? []) !== [] || ($module['listeners'] ?? []) !== []) {
                $errors[] = 'IntegrationContracts must not register source events or downstream listeners.';
            }

            if (! in_array('downstream_listeners', $module['integrations']['deferred'] ?? [], true)) {
                $errors[] = 'Downstream listener delivery must remain explicitly deferred.';
            }
        }

        try {
            $guard->assertSafePayload([
                'employeeId' => 1,
                'eventName' => 'EmployeeAssignmentChangedV1',
                'payload' => ['changedFields' => ['positionId']],
            ]);
        } catch (InvalidArgumentException $exception) {
            $errors[] = 'Privacy guard rejected known-safe payload: '.$exception->getMessage();
        }

        try {
            $guard->assertSafePayload(['salary' => 1]);
            $errors[] = 'Privacy guard did not reject forbidden salary field.';
        } catch (InvalidArgumentException) {
            //
        }

        $payload = [
            'passed' => $errors === [],
            'snapshotContracts' => count($contracts->snapshotContracts()),
            'eventContracts' => count($contracts->eventContracts()),
            'downstreamListeners' => 'deferred',
            'errors' => $errors,
        ];

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $errors === [] ? self::SUCCESS : self::FAILURE;
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $this->info('HR Integration Contracts validation passed.');
        $this->line("Snapshot contracts: {$payload['snapshotContracts']}");
        $this->line("Event contracts: {$payload['eventContracts']}");
        $this->line('Downstream listeners: deferred');

        return self::SUCCESS;
    }
}
