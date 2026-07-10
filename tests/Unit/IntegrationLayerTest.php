<?php

namespace Tests\Unit;

use App\Integration\Contracts\IntegrationAdapter;
use App\Integration\DTO\IntegrationMessageData;
use App\Integration\Support\EventIntegrationContext;
use App\Integration\Support\IntegrationRegistry;
use App\Shared\Events\BaseDomainEvent;
use App\Shared\Support\Result;
use App\Shared\ValueObjects\ModuleIdentifier;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class IntegrationLayerTest extends TestCase
{
    public function test_integration_message_wraps_domain_event_for_cross_module_boundaries(): void
    {
        $event = new IntegrationLayerFakeEvent(
            aggregateId: 'attendance-period-1',
            payload: ['period' => '2026-07'],
            metadata: ['actor_id' => 11, 'correlation_id' => 'corr-1'],
            eventId: 'event-1',
        );

        $message = IntegrationMessageData::fromDomainEvent(
            event: $event,
            source: ModuleIdentifier::parse('Attendance.AttendancePeriods'),
            target: ModuleIdentifier::parse('Payroll.PayrollPeriods'),
        );

        $this->assertSame('event-1', $message->eventId);
        $this->assertSame('integration-layer-fake-event', $message->eventName);
        $this->assertSame('Attendance.AttendancePeriods', $message->source->key());
        $this->assertSame('Payroll.PayrollPeriods', $message->target?->key());
        $this->assertSame('corr-1', $message->metadata['correlation_id']);
        $this->assertSame($message->toArray(), IntegrationMessageData::fromArray($message->toArray())->toArray());
    }

    public function test_integration_context_reads_domain_event_metadata(): void
    {
        $event = new IntegrationLayerFakeEvent(
            aggregateId: 'payroll-run-1',
            metadata: ['actor_id' => 15, 'correlation_id' => 'payroll-corr'],
        );

        $context = EventIntegrationContext::fromEvent(
            event: $event,
            source: ModuleIdentifier::parse('Payroll.PayrollRuns'),
            target: ModuleIdentifier::parse('Accounting.Journals'),
        );

        $this->assertSame('Payroll.PayrollRuns', $context->source()->key());
        $this->assertSame('Accounting.Journals', $context->target()?->key());
        $this->assertSame('payroll-corr', $context->correlationId());
        $this->assertSame(15, $context->actorId());
    }

    public function test_integration_registry_reads_module_dependencies_and_events(): void
    {
        $modulePath = app_path('Modules/TmpProject/ConsumerModule');

        File::deleteDirectory($modulePath);
        File::ensureDirectoryExists($modulePath);

        try {
            File::put($modulePath.'/module.php', <<<'PHP'
<?php

use Tests\Unit\IntegrationLayerFakeEvent;

return [
    'name' => 'ConsumerModule',
    'project' => 'TmpProject',
    'enabled' => true,
    'dependencies' => [
        'Console.AccessControls',
    ],
    'events' => [
        IntegrationLayerFakeEvent::class,
    ],
];
PHP);

            $registry = app(IntegrationRegistry::class);

            $dependencies = $registry->dependenciesFor('TmpProject.ConsumerModule');
            $events = $registry->publishedEventsFor('TmpProject.ConsumerModule');

            $this->assertSame('Console.AccessControls', $dependencies[0]->key());
            $this->assertContains(IntegrationLayerFakeEvent::class, $events);
        } finally {
            File::deleteDirectory(app_path('Modules/TmpProject'));
        }
    }

    public function test_integration_adapter_contract_can_return_result(): void
    {
        $adapter = new class implements IntegrationAdapter
        {
            public function source(): ModuleIdentifier
            {
                return ModuleIdentifier::parse('Attendance.AttendancePeriods');
            }

            public function target(): ModuleIdentifier
            {
                return ModuleIdentifier::parse('Payroll.PayrollPeriods');
            }

            public function canHandle(\App\Shared\Contracts\DomainEvent $event): bool
            {
                return $event instanceof IntegrationLayerFakeEvent;
            }

            public function handle(\App\Shared\Contracts\DomainEvent $event, \App\Integration\Contracts\IntegrationContext $context): Result
            {
                return Result::success([
                    'source' => $context->source()->key(),
                    'event' => $event->eventName(),
                ]);
            }
        };

        $event = new IntegrationLayerFakeEvent('aggregate-1');
        $context = EventIntegrationContext::fromEvent($event, $adapter->source(), $adapter->target());
        $result = $adapter->handle($event, $context);

        $this->assertTrue($adapter->canHandle($event));
        $this->assertTrue($result->successful());
        $this->assertSame('Attendance.AttendancePeriods', $result->value()['source']);
    }
}

class IntegrationLayerFakeEvent extends BaseDomainEvent {}
