# Testing Strategy

## Overview

Strategi testing untuk ERP system dengan arsitektur DDD-Lite Modular Monolith.

## Testing Pyramid

```
        /\
       /  \
      / E2E \
     /--------\
    / Integration \
   /----------------\
  /     Unit Tests     \
 /------------------------\
```

## Testing Levels

### 1. Unit Tests

**Tujuan:** Test individual classes dan methods dalam isolasi.

**Scope:**
- Actions
- Services
- DTOs
- Value Objects
- Domain Events
- Queries

**Framework:** PHPUnit

**Lokasi:** app/Modules/{Boundary}/{Module}/Tests/Unit/

**Contoh:**
```php
// Tests/Unit/CreateEmployeeActionTest.php
it('creates an employee with valid data', function () {
    $action = new CreateEmployeeAction();
    $data = CreateEmployeeData::from([
        'full_name' => 'John Doe',
        'email' => 'john@example.com',
        // ...
    ]);

    $employee = $action->execute($data);

    expect($employee)->toBeInstanceOf(Employee::class);
    expect($employee->employee_number)->toBeString();
});

it('throws exception for duplicate employee number', function () {
    // ...
})->throws(DuplicateEmployeeNumberException::class);
```

### 2. Feature Tests

**Tujuan:** Test HTTP endpoints dan request-response cycle.

**Scope:**
- Controllers
- Form Requests
- API Resources
- Routes
- Middleware

**Framework:** PHPUnit + Laravel testing helpers

**Lokasi:** app/Modules/{Boundary}/{Module}/Tests/Feature/

**Contoh:**
```php
// Tests/Feature/EmployeeControllerTest.php
it('can list employees', function () {
    $response = $this->actingAs($user)
        ->getJson('/api/v1/employees');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [['id', 'employee_number', 'full_name']],
            'pagination'
        ]);
});

it('validates employee creation request', function () {
    $response = $this->actingAs($user)
        ->postJson('/api/v1/employees', [
            'full_name' => '' // invalid
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('full_name');
});
```

### 3. Integration Tests

**Tujuan:** Test cross-module interactions dan kontrak.

**Scope:**
- Contract implementations
- Event listeners
- Cross-module queries
- Database transactions

**Framework:** PHPUnit

**Lokasi:** app/Modules/{Boundary}/{Module}/Tests/Integration/

**Contoh:**
```php
// Tests/Integration/EmployeeSnapshotProviderTest.php
it('returns employee snapshot with related data', function () {
    $employee = Employee::factory()->create();
    $provider = app(EmployeeSnapshotProvider::class);

    $snapshot = $provider->forEmployee($employee->id);

    expect($snapshot)->not->toBeNull();
    expect($snapshot->employee_number)->toBe($employee->employee_number);
    expect($snapshot->position)->toBe($employee->position->title);
});

it('returns null for non-existent employee', function () {
    $provider = app(EmployeeSnapshotProvider::class);
    expect($provider->forEmployee(99999))->toBeNull();
});
```

### 4. Architecture Tests

**Tujuan:** Test dependency rules dan architectural constraints.

**Scope:**
- Layer dependencies
- Module boundaries
- Namespace rules
- Forbidden imports

**Framework:** PHPUnit + PHPStan custom rules

**Lokasi:** tests/Architecture/

**Contoh:**
```php
// tests/Architecture/DependencyRulesTest.php
it('does not allow Presentation to depend on Infrastructure', function () {
    // Check that Presentation layer classes
    // do not import Infrastructure classes directly
})->group('architecture');

it('does not allow cross-module direct model access', function () {
    // Check that modules do not access
    // other modules models directly
})->group('architecture');
```

### 5. Frontend Tests

**Tujuan:** Test React components dan user interactions.

**Scope:**
- UI components
- Page components
- Form validation
- User interactions

**Framework:** Vitest + React Testing Library

**Lokasi:** resources/js/**/*.test.tsx

**Contoh:**
```tsx
// resources/js/Pages/HR/Employees/__tests__/EmployeeList.test.tsx
import { render, screen } from '@testing-library/react';
import { describe, it, expect } from 'vitest';
import EmployeeList from '../EmployeeList';

describe('EmployeeList', () => {
  it('renders employee list', () => {
    const employees = [
      { id: 1, full_name: 'John Doe', employee_number: 'EMP-001' }
    ];

    render(<EmployeeList employees={employees} />);

    expect(screen.getByText('John Doe')).toBeInTheDocument();
    expect(screen.getByText('EMP-001')).toBeInTheDocument();
  });

  it('shows empty state when no employees', () => {
    render(<EmployeeList employees={[]} />);

    expect(screen.getByText('No employees found')).toBeInTheDocument();
  });
});
```

## Test Coverage Targets

| Level | Target | Measurement |
|---|---|---|
| Unit Tests | >90% | PHPUnit coverage |
| Feature Tests | >85% | PHPUnit coverage |
| Integration Tests | >80% | PHPUnit coverage |
| Frontend Tests | >80% | Vitest coverage |
| Overall | >85% | Combined |

## Test Data Management

### Factories

Setiap modul memiliki factory untuk model-nya:

```php
// Database/Factories/EmployeeFactory.php
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'employee_number' => 'EMP-' . fake()->unique()->numberBetween(1000, 9999),
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            // ...
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
            'employment_status_id' => EmploymentStatus::active()->id,
        ]);
    }
}
```

### Seeders

Setiap modul memiliki seeder untuk data referensi:

```php
// Database/Seeders/EmploymentStatusSeeder.php
class EmploymentStatusSeeder extends Seeder
{
    public function run(): void
    {
        EmploymentStatus::firstOrCreate(['name' => 'Active']);
        EmploymentStatus::firstOrCreate(['name' => 'On Leave']);
        EmploymentStatus::firstOrCreate(['name' => 'Suspended']);
        EmploymentStatus::firstOrCreate(['name' => 'Terminated']);
    }
}
```

## CI/CD Testing

### GitHub Actions Workflow

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: secret
          MYSQL_DATABASE: testing
    steps:
      - uses: actions/checkout@v4
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - name: Install dependencies
        run: composer install
      - name: Run tests
        run: php artisan test --coverage
      - name: Upload coverage
        uses: codecov/codecov-action@v3
```

## Test Commands

```bash
# Run all tests
php artisan test

# Run specific module tests
php artisan test --filter=HRWorkLocation

# Run with coverage
php artisan test --coverage

# Run frontend tests
npm run test:frontend

# Run quality checks
composer quality:check
npm run quality:check
```

## Test Organization

```
tests/
├── Architecture/           # Architecture tests
├── Feature/               # Legacy feature tests (migrating to modules)
└── Unit/                  # Legacy unit tests (migrating to modules)

app/Modules/{Boundary}/{Module}/Tests/
├── Feature/               # Module feature tests
├── Integration/           # Module integration tests
└── Unit/                  # Module unit tests
```
