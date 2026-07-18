<?php

namespace Tests\Feature;

use App\Modules\HR\IntegrationContracts\Events\HRIntegrationEventV1;
use App\Modules\HR\IntegrationContracts\Support\HRIntegrationEventRegistry;
use App\Support\Modules\ModuleRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class HRIntegrationEventRegistryTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_registry_lists_event_contracts_and_source_modules(): void
    {
        $registry = app(HRIntegrationEventRegistry::class);

        $this->assertSame([
            'EmployeeCreatedV1',
            'EmployeeProfileUpdatedV1',
            'EmployeeAssignmentChangedV1',
            'EmployeeContractChangedV1',
            'EmployeeDocumentComplianceChangedV1',
            'EmployeeOnboardingActivatedV1',
            'EmployeeOnboardingCompletedV1',
            'EmployeeOffboardingReadyV1',
            'EmployeeOffboardingFinalizedV1',
            'EmploymentTerminatedV1',
        ], array_keys($registry->events()));

        $this->assertSame('HR.Employees', $registry->sourceModuleFor('EmployeeCreatedV1'));
        $this->assertSame('HR.EmployeeMovements', $registry->sourceModuleFor('EmployeeAssignmentChangedV1'));
        $this->assertSame('HR.Offboardings', $registry->sourceModuleFor('EmploymentTerminatedV1'));
    }

    public function test_event_registry_matches_module_manifest_and_keeps_downstream_listeners_deferred(): void
    {
        $registry = app(HRIntegrationEventRegistry::class);
        $module = ModuleRegistry::modules()->firstWhere('name', 'IntegrationContracts');

        $this->assertSame(array_keys($registry->events()), $module['integrations']['events']);
        $this->assertSame([], $module['events']);
        $this->assertSame([], $module['listeners']);
        $this->assertContains('downstream_listeners', $module['integrations']['deferred']);

        $registeredListeners = ModuleRegistry::eventListeners()
            ->filter(fn (array $listeners, string $event): bool => str_contains($event, 'IntegrationContracts'))
            ->all();

        $this->assertSame([], $registeredListeners);
    }

    public function test_event_contract_builds_standard_envelope_from_registry_mapping(): void
    {
        $event = HRIntegrationEventV1::make(
            eventName: 'EmployeeAssignmentChangedV1',
            occurredAt: CarbonImmutable::parse('2026-07-18 10:00:00', 'Asia/Jakarta'),
            actorUserId: 12,
            correlationId: 'movement-123',
            payload: [
                'employeeId' => 1001,
                'effectiveDate' => '2026-07-18',
                'changedFields' => ['positionId', 'workLocationId'],
            ],
        );

        $this->assertSame([
            'eventId' => 'hr-integration:EmployeeAssignmentChangedV1:1001:2026-07-18T03:00:00Z',
            'eventName' => 'EmployeeAssignmentChangedV1',
            'eventVersion' => 1,
            'occurredAt' => '2026-07-18T03:00:00Z',
            'sourceModule' => 'HR.EmployeeMovements',
            'actorUserId' => 12,
            'correlationId' => 'movement-123',
            'payload' => [
                'employeeId' => 1001,
                'effectiveDate' => '2026-07-18',
                'changedFields' => ['positionId', 'workLocationId'],
            ],
        ], $event->toArray());
    }

    public function test_event_contract_rejects_unknown_event_and_forbidden_payload(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown HR integration event contract: UnknownEventV1.');

        HRIntegrationEventV1::make(
            eventName: 'UnknownEventV1',
            occurredAt: CarbonImmutable::parse('2026-07-18 10:00:00', 'Asia/Jakarta'),
            actorUserId: null,
            correlationId: null,
            payload: ['employeeId' => 1001],
        );
    }

    public function test_event_contract_rejects_forbidden_payload_fields(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Integration payload contains forbidden field(s): salary, documentReference.');

        HRIntegrationEventV1::make(
            eventName: 'EmployeeDocumentComplianceChangedV1',
            occurredAt: CarbonImmutable::parse('2026-07-18 10:00:00', 'Asia/Jakarta'),
            actorUserId: null,
            correlationId: null,
            payload: [
                'employeeId' => 1001,
                'salary' => 1000000,
                'documentReference' => 'dms_secret',
            ],
        );
    }
}
