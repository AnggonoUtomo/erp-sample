# Technical Specification

## Overview

Spesifikasi teknis untuk ERP system dengan arsitektur DDD-Lite Modular Monolith.

## Tech Stack

### Backend

| Komponen | Versi | Tujuan |
|---|---|---|
| PHP | 8.2+ | Runtime |
| Laravel | 12.x | Framework |
| Eloquent ORM | Built-in | Database ORM |
| Spatie Permission | 8.x | RBAC |
| Spatie Media Library | 11.x | File management |
| Inertia.js | 2.x | Server-driven SPA |
| Laravel Sanctum | Built-in | API authentication |

### Frontend

| Komponen | Versi | Tujuan |
|---|---|---|
| React | 19.x | UI framework |
| TypeScript | 5.x | Type safety |
| Inertia.js | 2.x | Bridge Laravel-React |
| Tailwind CSS | 4.x | Styling |
| Radix UI | Latest | UI primitives |
| Lucide React | Latest | Icons |
| Sonner | Latest | Toast notifications |
| class-variance-authority | Latest | Variant patterns |

### Development Tools

| Komponen | Versi | Tujuan |
|---|---|---|
| Vite | 6.x | Build tool |
| PHPUnit | 11.x | Backend testing |
| Vitest | 4.x | Frontend testing |
| Laravel Pint | 1.x | Code formatting |
| ESLint | 9.x | JS/TS linting |
| Prettier | 3.x | Code formatting |
| Laravel Sail | 1.x | Docker development |

## Architecture Patterns

### DDD-Lite Layers

Presentation Layer (Controllers, Requests, Resources)
    |
    v
Application Layer (Actions, DTOs, Queries, Services, Contracts)
    |
    v
Domain Layer (Entities, Events, Exceptions, Services, ValueObjects)
    ^
    |
Infrastructure Layer (Models, Repositories, Observers, Providers)

### Module Structure

app/Modules/{Boundary}/{Module}/
  Application/
    Actions/
    DTO/
    Queries/
    Services/
    Contracts/
  Domain/
    Contracts/
    Entities/
    Events/
    Exceptions/
    Services/
    ValueObjects/
  Infrastructure/
    Persistence/
      Models/
      Repositories/
    Observers/
    Providers/
    External/
  Presentation/
    Controllers/
    Policies/
    Requests/
    Resources/
  Integration/
    Contracts/
    Listeners/
    Services/
  Database/
    Migrations/
    Factories/
    Seeders/
  Routes/
    web.php
    api.php
    console.php
  Tests/
    Feature/
    Integration/
    Unit/
  module.php
  permissions.php
  navigation.php
  ServiceProvider.php

## Coding Standards

### PHP

- PSR-12 coding style
- Type declarations (strict_types=1)
- Named arguments for clarity
- Readonly classes for DTOs and ValueObjects
- Final classes by default (open for extension only when needed)
- Constructor property promotion
- Match expressions over switch
- Arrow functions for simple closures

### TypeScript

- Strict mode enabled
- No implicit any
- Interface for object shapes
- Type alias for unions
- Const assertions for literal types
- ES modules only
- Absolute imports via tsconfig paths

### React

- Functional components with hooks
- TypeScript interfaces for props
- No inline styles (Tailwind classes)
- Server components where possible (Inertia)
- Client components only when needed (use client directive)

## Database Conventions

- Table names: snake_case plural (hr_employees)
- Primary keys: id (BIGINT AUTO_INCREMENT)
- Timestamps: created_at, updated_at, deleted_at (soft delete)
- Foreign keys: {table}_id (e.g., employee_id)
- Indexes: idx_{table}_{column}
- Migration naming: YYYY_MM_DD_000000_create_{table}_table.php

## API Conventions

- RESTful resource naming
- JSON:API inspired response format
- Pagination: page-based (page, per_page)
- Filtering: query parameters
- Sorting: sort parameter (field, -field for desc)
- Eager loading: include parameter
- Versioning: URL path (/api/v1/)

## Security Standards

- Password hashing: bcrypt (cost 12)
- API tokens: Laravel Sanctum
- CSRF protection: Laravel built-in
- Input validation: Form Requests
- SQL injection: Eloquent ORM (parameterized)
- XSS prevention: Blade escaping, React sanitization
- Rate limiting: 60 req/min per user

## Testing Standards

### Backend (PHPUnit)

- Unit tests: Test individual classes in isolation
- Feature tests: Test HTTP endpoints
- Integration tests: Test cross-module interactions
- Architecture tests: Test dependency rules
- Coverage target: >85%

### Frontend (Vitest)

- Unit tests: Test utility functions
- Component tests: Test React components
- Integration tests: Test page interactions
- Coverage target: >80%

## Performance Standards

- API response time: <200ms (95th percentile)
- Page load time: <2s (Lighthouse)
- Database query time: <50ms per query
- Memory usage: <256MB per request
- Concurrent users: Support 1000+

## Deployment Standards

- Environment: Production, Staging, Development
- Configuration: .env files (never commit secrets)
- Database migrations: Run on deployment
- Asset compilation: Vite build
- Cache: Config, route, view caching enabled
- Queue: Redis driver with worker processes
- Monitoring: Health checks, error tracking

## Documentation Standards

- PHPDoc blocks for public methods
- TypeScript JSDoc for exported functions
- API documentation via OpenAPI/Swagger
- Architecture documentation in docs/ folder
- README.md for each module
- CHANGELOG.md for version history
