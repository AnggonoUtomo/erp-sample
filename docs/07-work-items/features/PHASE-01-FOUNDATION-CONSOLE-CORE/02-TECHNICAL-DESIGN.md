"# Technical Design — Phase 1: Foundation + Console Core

## 1. Module Generator (MakeModuleCommand)

### Perubahan yang Diperlukan

File: `app/Support/Modules/Commands/MakeModuleCommand.php`

#### Stub Files Baru

Generator perlu membuat stub untuk 8 layer:

```php
// Stub: Application/Actions/Action.stub
<?php

declare(strict_types=1);

namespace DummyNamespace\Application\Actions;

abstract class DummyClass
{
    abstract public function handle(mixed $data): mixed;
}
```

```php
// Stub: Domain/Entities/Entity.stub
<?php

declare(strict_types=1);

namespace DummyNamespace\Domain\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

abstract class Entity extends Model
{
    use HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';
}
```

#### ULID Implementation

```php
// Migration stub
Schema::create('dummy_table', function (Blueprint $table) {
    $table->ulid('id')->primary();
    $table->timestamps();
    $table->softDeletes();
});
```

```php
// Model
class DummyModel extends Model
{
    use HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';
}
```

## 2. Shared Kernel

### Lokasi: `app/Shared/`

#### Base Classes

```php
// app/Shared/Models/BaseModel.php
<?php

declare(strict_types=1);

namespace App\Shared\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

abstract class BaseModel extends Model
{
    use HasUlids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function (Model $model) {
            if (empty($model->getKey())) {
                $model->{$model->getKeyName()} = \Illuminate\Support\Str::ulid()->toString();
            }
        });
    }
}
```

```php
// app/Shared/Repositories/BaseRepository.php
<?php

declare(strict_types=1);

namespace App\Shared\Repositories;

use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    public function __construct(
        protected Model $model
    ) {}

    public function find(string $id): ?Model
    {
        return $this->model->find($id);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->all();
    }

    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    public function update(string $id, array $data): bool
    {
        $record = $this->model->find($id);
        return $record ? $record->update($data) : false;
    }

    public function delete(string $id): bool
    {
        $record = $this->model->find($id);
        return $record ? $record->delete() : false;
    }
}
```

#### Value Objects

```php
// app/Shared/ValueObjects/Money.php
<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

readonly class Money
{
    public function __construct(
        public int $amount,
        public string $currency = 'IDR'
    ) {}

    public function toFloat(): float
    {
        return $this->amount / 100;
    }

    public function toString(): string
    {
        return number_format($this->toFloat(), 2, ',', '.');
    }
}
```

```php
// app/Shared/ValueObjects/DateRange.php
<?php

declare(strict_types=1);

namespace App\Shared\ValueObjects;

use Carbon\Carbon;

readonly class DateRange
{
    public function __construct(
        public Carbon $start,
        public ?Carbon $end = null
    ) {}

    public function contains(Carbon $date): bool
    {
        if ($this->end === null) {
            return $date->greaterThanOrEqualTo($this->start);
        }
        return $date->between($this->start, $this->end);
    }
}
```

## 3. Module Registry

### Lokasi: `app/Support/Modules/ModuleRegistry.php`

```php
<?php

declare(strict_types=1);

namespace App\Support\Modules;

class ModuleRegistry
{
    public function all(): array
    {
        $modules = [];
        $basePath = app_path('Modules');

        foreach ($this->getBoundaries() as $boundary) {
            $boundaryPath = $basePath . '/' . $boundary;
            if (!is_dir($boundaryPath)) continue;

            foreach (scandir($boundaryPath) as $module) {
                if ($module === '.' || $module === '..') continue;

                $modulePath = $boundaryPath . '/' . $module;
                if (!is_dir($modulePath)) continue;

                $moduleFile = $modulePath . '/module.php';
                if (!file_exists($moduleFile)) continue;

                $info = include $moduleFile;
                $info['path'] = $modulePath;
                $info['boundary'] = $boundary;
                $info['name'] = $module;

                $modules[] = $info;
            }
        }

        return $modules;
    }

    public function validate(string $boundary, string $module): bool
    {
        $modulePath = app_path('Modules/' . $boundary . '/' . $module);

        $requiredDirs = [
            'Application',
            'Domain',
            'Infrastructure',
            'Presentation',
            'Database',
            'Routes',
            'Tests'
        ];

        foreach ($requiredDirs as $dir) {
            if (!is_dir($modulePath . '/' . $dir)) {
                return false;
            }
        }

        return file_exists($modulePath . '/module.php');
    }

    private function getBoundaries(): array
    {
        return ['HR', 'Console', 'DocumentManagement'];
    }
}
```

## 4. Console Modules Structure

### UserManagements

```
app/Modules/Console/UserManagements/
├── Application/
│   ├── Actions/
│   │   ├── CreateUserAction.php
│   │   ├── UpdateUserAction.php
│   │   ├── DeleteUserAction.php
│   │   └── ImpersonateUserAction.php
│   ├── DTO/
│   │   └── UserData.php
│   ├── Queries/
│   │   └── FindUserQuery.php
│   ├── Services/
│   │   └── UserApplicationService.php
│   └── Contracts/
│       └── UserRepositoryInterface.php
├── Domain/
│   ├── Contracts/
│   │   └── UserServiceInterface.php
│   ├── Entities/
│   │   └── User.php
│   ├── Events/
│   │   ├── UserCreated.php
│   │   └── UserDeleted.php
│   ├── Exceptions/
│   │   └── UserNotFoundException.php
│   ├── Services/
│   │   └── UserDomainService.php
│   └── ValueObjects/
│       └── Email.php
├── Infrastructure/
│   ├── Persistence/
│   │   ├── Models/
│   │   │   └── User.php
│   │   └── Repositories/
│   │       └── EloquentUserRepository.php
│   ├── Observers/
│   │   └── UserObserver.php
│   ├── Providers/
│   │   └── UserInfrastructureProvider.php
│   └── External/
├── Presentation/
│   ├── Controllers/
│   │   └── UserController.php
│   ├── Policies/
│   │   └── UserPolicy.php
│   ├── Requests/
│   │   ├── StoreUserRequest.php
│   │   └── UpdateUserRequest.php
│   └── Resources/
│       └── UserResource.php
├── Integration/
│   ├── Contracts/
│   │   └── UserSnapshotProvider.php
│   ├── Listeners/
│   └── Services/
├── Database/
│   ├── Factories/
│   │   └── UserFactory.php
│   ├── Migrations/
│   │   └── create_users_table.php
│   └── Seeders/
│       └── UserSeeder.php
├── Routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── Tests/
│   ├── Feature/
│   │   └── UserControllerTest.php
│   ├── Integration/
│   │   └── UserRepositoryTest.php
│   └── Unit/
│       └── CreateUserActionTest.php
├── module.php
├── permissions.php
├── navigation.php
├── ServiceProvider.php
└── README.md
```

### SystemSettings

Structure similar to UserManagements with:
- Settings CRUD
- Email configuration
- Branding configuration
- Maintenance mode

### AuditLogs

Structure similar with:
- AuditLog model
- AuditObserver for automatic logging
- AuditLogService for queries
- Event listeners

## 5. Testing Strategy

### Unit Tests

```php
// Tests/Unit/CreateUserActionTest.php
it('creates a user with valid data', function () {
    $action = new CreateUserAction();
    $data = UserData::from([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    $user = $action->execute($data);

    expect($user)->toBeInstanceOf(User::class);
    expect($user->id)->toBeString(); // ULID
    expect($user->email)->toBe('test@example.com');
});
```

### Feature Tests

```php
// Tests/Feature/UserControllerTest.php
it('can list users', function () {
    $response = $this->actingAs($admin)
        ->getJson('/api/v1/users');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [['id', 'name', 'email']],
            'pagination'
        ]);
});
```

### Integration Tests

```php
// Tests/Integration/UserRepositoryTest.php
it('can find user by ULID', function () {
    $user = User::factory()->create();

    $repository = app(UserRepositoryInterface::class);
    $found = $repository->find($user->id);

    expect($found)->not->toBeNull();
    expect($found->id)->toBe($user->id);
});
```

## 6. Migration Strategy

### Existing Data

Untuk modul yang sudah ada dengan BIGINT, perlu migration:

```php
// migrate_user_ids_to_ulid.php
public function up(): void
{
    // 1. Add ulid column
    Schema::table('users', function (Blueprint $table) {
        $table->ulid('new_id')->nullable();
    });

    // 2. Generate ULID for existing records
    DB::table('users')->chunk(100, function ($users) {
        foreach ($users as $user) {
            DB::table('users')
                ->where('id', $user->id)
                ->update(['new_id' => Str::ulid()->toString()]);
        }
    });

    // 3. Update foreign keys
    // ... update all related tables

    // 4. Drop old id, rename new_id
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('id');
    });

    Schema::table('users', function (Blueprint $table) {
        $table->renameColumn('new_id', 'id');
        $table->primary('id');
    });
}
```

## 7. Checklist Implementasi

### Week 1

- [ ] Update MakeModuleCommand dengan stub DDD-Lite
- [ ] Tambahkan ULID support di generator
- [ ] Buat BaseModel dengan HasUlids
- [ ] Buat BaseRepository
- [ ] Buat ValueObjects (Money, DateRange, Status)
- [ ] Update ModuleRegistry untuk struktur baru
- [ ] Buat module validation command

### Week 2

- [ ] Generate UserManagements dengan struktur baru
- [ ] Generate SystemSettings dengan struktur baru
- [ ] Generate AuditLogs dengan struktur baru
- [ ] Buat migration untuk users table (ULID)
- [ ] Buat migration untuk audit_logs table (ULID)
- [ ] Buat migration for system_settings table (ULID)
- [ ] Implementasi User CRUD
- [ ] Implementasi User impersonation
- [ ] Implementasi SystemSettings CRUD
- [ ] Implementasi AuditLog observer
- [ ] Buat Unit tests
- [ ] Buat Feature tests
- [ ] Buat Integration tests
- [ ] Run all tests, pastikan pass
"