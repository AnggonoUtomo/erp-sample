# Feature Specification - Phase 1: Foundation + Console Core

> **SUPERSEDED:** Dokumen historis. Jangan gunakan status, scope, ULID, delapan layer, atau task di dalamnya untuk coding. Lihat `WORK-ITEM-README.md`, ADR-0001, dan `MIG-ID-001`.

> Dokumen acuan kerja untuk Phase 1 implementation.

## Metadata

`yaml
work_item: PHASE-01-FOUNDATION-CONSOLE-CORE
parent: ARC-DDD-LITE-001
status: proposed
priority: P0
owner: unassigned
last_updated: 2026-08-12
`

## Ringkasan

Phase 1 bertujuan untuk setup foundation arsitektur DDD-Lite dan konversi 3 modul Console core:
- Console/UserManagements
- Console/SystemSettings
- Console/AuditLogs

## Tujuan

1. Module generator menghasilkan struktur DDD-Lite dengan ULID
2. Shared Kernel (ValueObjects, base classes) functional
3. Module registry dan validation berjalan
4. 3 modul Console core mengikuti struktur DDD-Lite
5. Kontrak publik untuk setiap modul terdefinisi

## Scope

### Dalam Scope

1. **Module Generator**
   - Update MakeModuleCommand untuk generate struktur DDD-Lite
   - Tambahkan ULID support untuk primary key
   - Generate 8 layer: Application, Domain, Infrastructure, Presentation, Integration, Database, Routes, Tests

2. **Shared Kernel**
   - Base Model dengan ULID
   - Base Repository
   - ValueObjects: Money, DateRange, Status
   - Base Action
   - Base Event

3. **Module Registry**
   - Update ModuleRegistry untuk struktur baru
   - Module validation
   - Module discovery

4. **Console/UserManagements**
   - User CRUD dengan struktur DDD-Lite
   - User impersonation
   - Permission management

5. **Console/SystemSettings**
   - System configuration
   - Email settings
   - Branding settings
   - Maintenance mode

6. **Console/AuditLogs**
   - Audit trail untuk semua modul
   - Event listeners
   - Query dan reporting

### Di Luar Scope

- Frontend React pages (akan dikerjakan di Phase 4)
- API documentation
- Performance optimization
- Security hardening

## Kriteria Penerimaan

### Module Generator

- [ ] php artisan make:module Console/TestModule generate struktur DDD-Lite
- [ ] ULID digunakan untuk semua primary key
- [ ] 8 layer ter-generate dengan file stub yang benar
- [ ] ServiceProvider ter-register otomatis
- [ ] Routes file ter-load

### Shared Kernel

- [ ] BaseModel dengan ULID trait
- [ ] BaseRepository interface
- [ ] ValueObjects: Money, DateRange, Status
- [ ] BaseAction abstract class
- [ ] BaseEvent abstract class

### Module Registry

- [ ] ModuleRegistry mendeteksi modul dengan struktur baru
- [ ] Module validation berjalan
- [ ] Module list command menampilkan info lengkap

### Console Modules

- [ ] UserManagements: CRUD user, impersonation, permissions
- [ ] SystemSettings: CRUD settings, email test, branding
- [ ] AuditLogs: Audit trail, query, retention

## Struktur Target

app/Modules/Console/UserManagements/
|-- Application/
|   |-- Actions/
|   |   |-- CreateUserAction.php
|   |   |-- UpdateUserAction.php
|   |   |-- DeleteUserAction.php
|   |   |-- ImpersonateUserAction.php
|   |-- DTO/
|   |   |-- UserData.php
|   |-- Queries/
|   |   |-- FindUserQuery.php
|   |-- Services/
|   |   |-- UserApplicationService.php
|   |-- Contracts/
|   |   |-- UserRepositoryInterface.php
|-- Domain/
|   |-- Contracts/
|   |   |-- UserServiceInterface.php
|   |-- Entities/
|   |   |-- User.php
|   |-- Events/
|   |   |-- UserCreated.php
|   |   |-- UserDeleted.php
|   |-- Exceptions/
|   |   |-- UserNotFoundException.php
|   |-- Services/
|   |   |-- UserDomainService.php
|   |-- ValueObjects/
|   |   |-- Email.php
|-- Infrastructure/
|   |-- Persistence/
|   |   |-- Models/
|   |   |   |-- User.php
|   |   |-- Repositories/
|   |   |   |-- EloquentUserRepository.php
|   |-- Observers/
|   |   |-- UserObserver.php
|   |-- Providers/
|   |   |-- UserInfrastructureProvider.php
|   |-- External/
|-- Presentation/
|   |-- Controllers/
|   |   |-- UserController.php
|   |-- Policies/
|   |   |-- UserPolicy.php
|   |-- Requests/
|   |   |-- StoreUserRequest.php
|   |   |-- UpdateUserRequest.php
|   |-- Resources/
|   |   |-- UserResource.php
|-- Integration/
|   |-- Contracts/
|   |   |-- UserSnapshotProvider.php
|   |-- Listeners/
|   |-- Services/
|-- Database/
|   |-- Factories/
|   |   |-- UserFactory.php
|   |-- Migrations/
|   |   |-- create_users_table.php
|   |-- Seeders/
|   |   |-- UserSeeder.php
|-- Routes/
|   |-- web.php
|   |-- api.php
|   |-- console.php
|-- Tests/
|   |-- Feature/
|   |   |-- UserControllerTest.php
|   |-- Integration/
|   |   |-- UserRepositoryTest.php
|   |-- Unit/
|   |   |-- CreateUserActionTest.php
|-- module.php
|-- permissions.php
|-- navigation.php
|-- ServiceProvider.php
|-- README.md

## Dependencies

| Task | Depends On |
|------|------------|
| Module Generator | - |
| Shared Kernel | Module Generator |
| Module Registry | Shared Kernel |
| UserManagements | Module Generator, Shared Kernel |
| SystemSettings | Module Generator, Shared Kernel |
| AuditLogs | Module Generator, Shared Kernel, UserManagements |

## Timeline

| Week | Tasks |
|------|-------|
| Week 1 | Module Generator, Shared Kernel, Module Registry |
| Week 2 | UserManagements, SystemSettings, AuditLogs |

## Success Criteria

1. php artisan make:module Console/TestModule generate struktur DDD-Lite [OK]
2. ULID digunakan untuk semua primary key [OK]
3. All modules follow DDD-Lite structure [OK]
4. Contracts defined and bound [OK]
5. All tests pass [OK]

## Keterlacakan

- Terkait dengan ARC-DDD-LITE-001 (parent)
- Terkait dengan IMPLEMENTATION-PLAN.md (Phase 1)
- Terkait dengan DATABASE-DESIGN.md (ULID)
