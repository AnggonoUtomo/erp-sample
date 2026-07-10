# Shared Kernel

Shared Kernel adalah lapisan kecil untuk konsep yang benar-benar dipakai lintas project/module. Tujuannya membuat komunikasi antar project lebih stabil tanpa membuat module saling bergantung ke detail internal.

## Struktur

```txt
app/Shared/
  Contracts/
    ArrayableData.php
    DomainEvent.php
    DomainEventDispatcher.php
    DomainEventSubscriber.php
  DTO/
    DataObject.php
  Enums/
    SortDirection.php
  Events/
    BaseDomainEvent.php
    DispatchesDomainEvents.php
    DomainEventMap.php
    LaravelDomainEventDispatcher.php
    PublishesDomainEvents.php
  Exceptions/
    SharedKernelException.php
  Support/
    Result.php
  ValueObjects/
    DateRange.php
    ModuleIdentifier.php
    Money.php
```

## Aturan Pemakaian

Masukkan sesuatu ke Shared Kernel hanya jika:

- Dipakai oleh lebih dari satu project.
- Tidak membawa aturan bisnis spesifik satu module.
- Stabil dan jarang berubah.
- Aman dijadikan contract publik.

Jangan masukkan ke Shared Kernel jika:

- Hanya dipakai satu module.
- Mengandung query/model internal module.
- Mengandung workflow bisnis spesifik.
- Membuat project lain harus tahu detail implementasi module.

## Contracts

### `ArrayableData`

Contract sederhana untuk object yang bisa diubah menjadi array.

```php
use App\Shared\Contracts\ArrayableData;

final readonly class ExampleData implements ArrayableData
{
    public function toArray(): array
    {
        return [];
    }
}
```

### `DomainEvent`

Contract standar untuk event lintas module/project.

```php
use App\Shared\Contracts\DomainEvent;

function publish(DomainEvent $event): void
{
    logger()->info($event->eventName(), $event->toArray());
}
```

### `DomainEventDispatcher`

Contract untuk mem-publish domain event. Implementasi default memakai Laravel Event melalui `LaravelDomainEventDispatcher`.

```php
use App\Shared\Contracts\DomainEventDispatcher;

final readonly class JournalService
{
    public function __construct(
        private DomainEventDispatcher $events,
    ) {}
}
```

### `DomainEventSubscriber`

Contract untuk listener domain event lintas project.

```php
use App\Shared\Contracts\DomainEvent;
use App\Shared\Contracts\DomainEventSubscriber;

final class AddJournalPostedActivity implements DomainEventSubscriber
{
    public function handle(DomainEvent $event): void
    {
        //
    }
}
```

## DTO

### `DataObject`

Base DTO untuk data transfer object yang punya factory `fromArray`.

```php
use App\Shared\DTO\DataObject;

final readonly class AccountData extends DataObject
{
    public function __construct(
        public string $code,
        public string $name,
    ) {}

    public static function fromArray(array $payload): static
    {
        return new static(
            code: (string) $payload['code'],
            name: (string) $payload['name'],
        );
    }

    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
        ];
    }
}
```

## Domain Events

Gunakan `BaseDomainEvent` untuk event bernama yang stabil.

```php
use App\Shared\Events\BaseDomainEvent;

final class JournalPosted extends BaseDomainEvent {}

$event = new JournalPosted(
    aggregateId: 'journal-uuid',
    payload: ['number' => 'JV-0001'],
    metadata: ['actor_id' => auth()->id()],
);
```

Output event:

```php
$event->toArray();
```

berisi:

- `id`
- `name`
- `aggregate_id`
- `payload`
- `metadata`
- `occurred_at`

Untuk aggregate/service yang perlu menahan event sebelum dispatch, gunakan trait:

```php
use App\Shared\Events\DispatchesDomainEvents;

final class JournalAggregate
{
    use DispatchesDomainEvents;

    public function post(): void
    {
        $this->recordDomainEvent(new JournalPosted('journal-uuid'));
    }
}
```

Lalu publish event yang sudah direkam:

```php
use App\Shared\Contracts\DomainEventDispatcher;

final readonly class JournalService
{
    public function __construct(
        private DomainEventDispatcher $events,
    ) {}

    public function post(JournalAggregate $journal): void
    {
        $journal->post();
        $journal->publishRecordedDomainEvents($this->events);
    }
}
```

## Mendaftarkan Listener Domain Event

Setiap module bisa mendeklarasikan event publik dan listener di `module.php`.

```php
use App\Modules\Accounting\Journals\Events\JournalPosted;
use App\Modules\Console\ActivityCenters\Listeners\CreateJournalPostedActivity;

return [
    'name' => 'ActivityCenters',
    'project' => 'Console',
    'events' => [],
    'listeners' => [
        JournalPosted::class => [
            CreateJournalPostedActivity::class,
        ],
    ],
];
```

Module yang menerbitkan event sebaiknya mengisi `events`:

```php
use App\Modules\Accounting\Journals\Events\JournalPosted;
use App\Modules\Accounting\Journals\Events\JournalReversed;

return [
    'name' => 'Journals',
    'project' => 'Accounting',
    'events' => [
        JournalPosted::class,
        JournalReversed::class,
    ],
    'listeners' => [],
];
```

`ModuleServiceProvider` akan membaca `listeners` dari semua manifest module dan mendaftarkannya ke Laravel Event.

## Naming Convention

- Event memakai past tense: `JournalPosted`, `UserInvited`, `PayrollRunApproved`.
- Event berada di `Events/`.
- Listener berada di `Listeners/`.
- Listener lintas project sebaiknya hanya membaca `payload()` dan `metadata()`, bukan model internal event source.
- `aggregateId` wajib berisi identifier record utama.
- `payload` berisi data publik yang aman dibaca module lain.
- `metadata` berisi konteks seperti `actor_id`, `source_module`, `correlation_id`, atau `request_id`.

## Value Objects

### `ModuleIdentifier`

Dipakai untuk identifier module dalam format `Project.Module`.

```php
use App\Shared\ValueObjects\ModuleIdentifier;

$identifier = ModuleIdentifier::parse('Accounting.ChartOfAccounts');

$identifier->key(); // Accounting.ChartOfAccounts
$identifier->namespace(); // App\Modules\Accounting\ChartOfAccounts
$identifier->frontendPath(); // accounting/chart-of-accounts
```

### `DateRange`

Dipakai untuk filter, periode, dan report lintas project.

```php
use App\Shared\ValueObjects\DateRange;

$range = DateRange::fromStrings('2026-07-01', '2026-07-31');

$range->contains('2026-07-15'); // true
$range->days(); // 31
```

### `Money`

Dipakai untuk representasi uang berbasis minor amount.

```php
use App\Shared\ValueObjects\Money;

$amount = Money::fromMajor('150000.50', 'IDR');

$amount->minorAmount; // 15000050
$amount->currency; // IDR
```

Catatan: simpan nilai uang sebagai `minorAmount` di contract/event agar presisi lebih aman.

## Support

### `Result`

Dipakai untuk return object use case yang butuh status sukses/gagal.

```php
use App\Shared\Support\Result;

return Result::success(['id' => 1]);
return Result::failure('Periode sudah ditutup.');
```

## Integrasi Dengan Module

Module boleh memakai Shared Kernel untuk:

- DTO base.
- Value object.
- Domain event base.
- Result object.
- Interface publik lintas project.

Module tidak boleh memakai Shared Kernel sebagai tempat:

- Model Eloquent.
- Query bisnis.
- Controller.
- Service spesifik module.
- Policy spesifik module.

## Langkah Berikutnya

Setelah Shared Kernel ini, langkah arsitektur berikutnya:

1. Buat Integration Layer untuk listener, adapter, dan projector lintas project.
2. Buat anti-corruption layer agar project besar seperti Payroll tidak membaca internal Attendance secara langsung.
