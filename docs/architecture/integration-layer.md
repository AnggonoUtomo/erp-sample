# Integration Layer

Integration Layer adalah jembatan antar project/module. Layer ini dipakai agar module tidak saling membaca model, query, atau service internal module lain secara langsung.

Contoh tujuan:

- `Attendance` menutup periode dan mengirim snapshot ke `Payroll`.
- `Payroll` menyetujui payroll run dan mengirim draft journal ke `Accounting`.
- `Accounting` mem-posting journal dan mengirim timeline event ke `Console`.

## Struktur

```txt
app/Integration/
  Contracts/
    IntegrationAdapter.php
    IntegrationContext.php
    IntegrationProjector.php
  DTO/
    IntegrationMessageData.php
  Support/
    EventIntegrationContext.php
    IntegrationRegistry.php
```

Module baru juga dibuat dengan folder:

```txt
app/Modules/{Project}/{Module}/
  Events/
  Integrations/
  Listeners/
```

## Konsep

Integration Layer membagi komunikasi menjadi tiga bagian:

- **Domain Event**: sinyal bahwa sesuatu sudah terjadi.
- **Integration Message**: bentuk data publik yang aman dikirim lintas boundary.
- **Adapter/Projector**: penerjemah dari event source ke kebutuhan target.

Dengan pola ini, `Payroll` tidak perlu import model internal `Attendance`. Payroll cukup membaca message/snapshot yang dikirim oleh adapter.

## Integration Message

`IntegrationMessageData` membungkus domain event menjadi pesan lintas module.

```php
use App\Integration\DTO\IntegrationMessageData;
use App\Shared\ValueObjects\ModuleIdentifier;

$message = IntegrationMessageData::fromDomainEvent(
    event: $event,
    source: ModuleIdentifier::parse('Attendance.AttendancePeriods'),
    target: ModuleIdentifier::parse('Payroll.PayrollPeriods'),
);
```

Isi message:

- `event_id`
- `event_name`
- `aggregate_id`
- `source`
- `target`
- `payload`
- `metadata`
- `occurred_at`

## Integration Context

`EventIntegrationContext` membawa konteks komunikasi, seperti source, target, actor, dan correlation id.

```php
use App\Integration\Support\EventIntegrationContext;
use App\Shared\ValueObjects\ModuleIdentifier;

$context = EventIntegrationContext::fromEvent(
    event: $event,
    source: ModuleIdentifier::parse('Payroll.PayrollRuns'),
    target: ModuleIdentifier::parse('Accounting.Journals'),
);
```

Gunakan `correlation_id` agar chain antar project bisa ditelusuri.

```php
metadata: [
    'actor_id' => auth()->id(),
    'correlation_id' => (string) Str::uuid(),
]
```

## Integration Adapter

Adapter menerjemahkan event source menjadi aksi target.

```php
use App\Integration\Contracts\IntegrationAdapter;
use App\Integration\Contracts\IntegrationContext;
use App\Shared\Contracts\DomainEvent;
use App\Shared\Support\Result;
use App\Shared\ValueObjects\ModuleIdentifier;

final class AttendanceToPayrollAdapter implements IntegrationAdapter
{
    public function source(): ModuleIdentifier
    {
        return ModuleIdentifier::parse('Attendance.AttendancePeriods');
    }

    public function target(): ModuleIdentifier
    {
        return ModuleIdentifier::parse('Payroll.PayrollPeriods');
    }

    public function canHandle(DomainEvent $event): bool
    {
        return $event instanceof AttendancePeriodClosed;
    }

    public function handle(DomainEvent $event, IntegrationContext $context): Result
    {
        // Build payroll input snapshot here.
        return Result::success();
    }
}
```

## Integration Projector

Projector dipakai untuk membuat read model/snapshot dari integration message.

```php
use App\Integration\Contracts\IntegrationContext;
use App\Integration\Contracts\IntegrationProjector;
use App\Integration\DTO\IntegrationMessageData;
use App\Shared\Support\Result;

final class PayrollAttendanceSnapshotProjector implements IntegrationProjector
{
    public function project(IntegrationMessageData $message, IntegrationContext $context): Result
    {
        // Persist read model/snapshot target.
        return Result::success();
    }
}
```

## Integration Registry

`IntegrationRegistry` membaca manifest module untuk dependency, published events, dan listeners.

```php
use App\Integration\Support\IntegrationRegistry;

$registry = app(IntegrationRegistry::class);

$registry->dependenciesFor('Payroll.PayrollRuns');
$registry->publishedEventsFor('Attendance.AttendancePeriods');
$registry->listenersFor(AttendancePeriodClosed::class);
```

## Manifest Module

`module.php` mendukung field:

```php
return [
    'dependencies' => [
        'Console.AccessControls',
    ],
    'events' => [
        AttendancePeriodClosed::class,
    ],
    'listeners' => [
        AttendancePeriodClosed::class => [
            BuildPayrollAttendanceSnapshot::class,
        ],
    ],
    'integrations' => [
        AttendanceToPayrollAdapter::class,
        PayrollAttendanceSnapshotProjector::class,
    ],
];
```

## Aturan Boundary

Yang boleh dilakukan:

- Event source mengirim `payload` publik.
- Target membaca `IntegrationMessageData`.
- Adapter membuat snapshot/read model target.
- Listener/projector menjalankan proses asynchronous via queue jika berat.

Yang perlu dihindari:

- Target import model internal source.
- Target memanggil service internal source.
- Payload event berisi seluruh model Eloquent.
- Adapter menyimpan data tanpa transaction saat operasi kompleks.
- Cross-project query langsung tanpa contract.

## Contoh Alur Attendance ke Payroll

```txt
AttendancePeriodClosed
  -> BuildPayrollAttendanceSnapshot listener
  -> AttendanceToPayrollAdapter
  -> IntegrationMessageData
  -> PayrollAttendanceSnapshotProjector
  -> Payroll input locked/ready
```

## Contoh Alur Payroll ke Accounting

```txt
PayrollRunApproved
  -> GeneratePayrollJournal listener
  -> PayrollToAccountingAdapter
  -> IntegrationMessageData
  -> Accounting draft journal
```

## Checklist Implementasi Integrasi

- Event source ada di `Events/`.
- Event masuk ke `events` di `module.php`.
- Listener target ada di `Listeners/`.
- Adapter/projector ada di `Integrations/`.
- Listener didaftarkan di `listeners` di `module.php`.
- Adapter/projector didaftarkan di `integrations` di `module.php`.
- Payload event tidak mengandung model internal.
- Test adapter/projector tersedia.
- Jika proses berat, listener menjalankan queue job.
