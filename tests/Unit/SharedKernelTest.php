<?php

namespace Tests\Unit;

use App\Shared\Contracts\DomainEvent;
use App\Shared\Contracts\DomainEventDispatcher;
use App\Shared\Contracts\DomainEventSubscriber;
use App\Shared\Events\BaseDomainEvent;
use App\Shared\Events\DispatchesDomainEvents;
use App\Shared\Exceptions\SharedKernelException;
use App\Shared\Support\Result;
use App\Shared\ValueObjects\DateRange;
use App\Shared\ValueObjects\ModuleIdentifier;
use App\Shared\ValueObjects\Money;
use App\Support\Modules\ModuleRegistry;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Tests\TestCase;

class SharedKernelTest extends TestCase
{
    public function test_module_identifier_normalizes_module_keys(): void
    {
        $identifier = ModuleIdentifier::parse('accounting/chart-of-accounts');

        $this->assertSame('Accounting.ChartOfAccounts', $identifier->key());
        $this->assertSame('App\\Modules\\Accounting\\ChartOfAccounts', $identifier->namespace());
        $this->assertSame('accounting/chart-of-accounts', $identifier->frontendPath());
    }

    public function test_date_range_validates_order_and_counts_days(): void
    {
        $range = DateRange::fromStrings('2026-07-01', '2026-07-03');

        $this->assertSame(3, $range->days());
        $this->assertTrue($range->contains('2026-07-02'));
        $this->assertFalse($range->contains('2026-07-04'));

        $this->expectException(SharedKernelException::class);

        DateRange::fromStrings('2026-07-03', '2026-07-01');
    }

    public function test_money_keeps_currency_safe_arithmetic(): void
    {
        $total = Money::fromMajor('1000', 'IDR')->add(Money::fromMajor('250.50', 'IDR'));

        $this->assertSame(125050, $total->minorAmount);
        $this->assertSame('IDR', $total->currency);

        $this->expectException(SharedKernelException::class);

        $total->subtract(Money::fromMajor(10, 'USD'));
    }

    public function test_result_wraps_success_and_failure(): void
    {
        $success = Result::success(['id' => 1], ['source' => 'test']);
        $failure = Result::failure('Invalid state');

        $this->assertTrue($success->successful());
        $this->assertSame(['id' => 1], $success->value());
        $this->assertSame(['source' => 'test'], $success->meta());
        $this->assertTrue($failure->failed());
        $this->assertSame('Invalid state', $failure->error());
    }

    public function test_domain_event_has_stable_identity_and_payload(): void
    {
        $occurredAt = CarbonImmutable::parse('2026-07-02 10:00:00');
        $event = new SharedKernelFakeEvent('journal-1', ['status' => 'posted'], ['actor_id' => 7], 'event-1', $occurredAt);

        $this->assertSame('event-1', $event->eventId());
        $this->assertSame('event-1', $event->eventId());
        $this->assertSame('shared-kernel-fake-event', $event->eventName());
        $this->assertSame('journal-1', $event->aggregateId());
        $this->assertSame(['status' => 'posted'], $event->payload());
        $this->assertSame(['actor_id' => 7], $event->metadata());
        $this->assertSame('2026-07-02T10:00:00.000000Z', $event->toArray()['occurred_at']);
    }

    public function test_domain_event_recorder_releases_and_clears_events(): void
    {
        $recorder = new class
        {
            use DispatchesDomainEvents;

            public function record(BaseDomainEvent $event): void
            {
                $this->recordDomainEvent($event);
            }
        };

        $recorder->record(new SharedKernelFakeEvent('aggregate-1'));

        $this->assertCount(1, $recorder->releaseDomainEvents());
        $this->assertCount(0, $recorder->releaseDomainEvents());
    }

    public function test_domain_event_dispatcher_uses_laravel_events(): void
    {
        Event::fake([SharedKernelFakeEvent::class]);

        app(DomainEventDispatcher::class)->dispatch(new SharedKernelFakeEvent('aggregate-1'));

        Event::assertDispatched(SharedKernelFakeEvent::class);
    }

    public function test_module_manifest_can_register_domain_event_listeners(): void
    {
        $backendRoot = storage_path('framework/testing/modules/'.Str::uuid());
        $modulePath = $backendRoot.'/TmpProject/EventfulModule';

        config(['modules.backend_root' => $backendRoot]);

        File::deleteDirectory($modulePath);
        File::ensureDirectoryExists($modulePath);

        try {
            File::put($modulePath.'/module.php', <<<'PHP'
<?php

use Tests\Unit\SharedKernelFakeEvent;
use Tests\Unit\SharedKernelFakeSubscriber;

return [
    'name' => 'EventfulModule',
    'project' => 'TmpProject',
    'enabled' => true,
    'listeners' => [
        SharedKernelFakeEvent::class => [
            SharedKernelFakeSubscriber::class,
        ],
    ],
];
PHP);

            $listeners = ModuleRegistry::eventListeners();

            $this->assertContains(SharedKernelFakeSubscriber::class, $listeners->get(SharedKernelFakeEvent::class));
        } finally {
            File::deleteDirectory($backendRoot);
        }
    }
}

class SharedKernelFakeEvent extends BaseDomainEvent {}

class SharedKernelFakeSubscriber implements DomainEventSubscriber
{
    public function handle(DomainEvent $event): void
    {
        //
    }
}
