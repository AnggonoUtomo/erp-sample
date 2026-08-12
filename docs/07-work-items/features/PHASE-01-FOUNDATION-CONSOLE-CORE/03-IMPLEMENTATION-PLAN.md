"# Implementation Plan — Phase 1: Foundation + Console Core

## Sprint 1: Foundation (Week 1)

### Day 1-2: Module Generator

**Tasks:**
1. Analisis MakeModuleCommand existing
2. Buat struktur stub DDD-Lite (8 layer)
3. Tambahkan ULID support
4. Test generator dengan modul dummy

**Deliverables:**
- MakeModuleCommand updated
- Stub files untuk 8 layer
- Test modul dummy

**Acceptance Criteria:**
- `php artisan make:module Console/Test` generate struktur lengkap
- ULID digunakan untuk primary key
- ServiceProvider ter-register

---

### Day 3-4: Shared Kernel

**Tasks:**
1. Buat BaseModel dengan HasUlids
2. Buat BaseRepository
3. Buat ValueObjects (Money, DateRange, Status)
4. Buat BaseAction
5. Buat BaseEvent

**Deliverables:**
- app/Shared/Models/BaseModel.php
- app/Shared/Repositories/BaseRepository.php
- app/Shared/ValueObjects/Money.php
- app/Shared/ValueObjects/DateRange.php
- app/Shared/ValueObjects/Status.php
- app/Shared/Actions/BaseAction.php
- app/Shared/Events/BaseEvent.php

**Acceptance Criteria:**
- BaseModel bisa digunakan untuk create model dengan ULID
- BaseRepository bisa CRUD
- ValueObjects immutable dan readonly

---

### Day 5-6: Module Registry

**Tasks:**
1. Update ModuleRegistry untuk struktur baru
2. Tambahkan module validation
3. Buat module:list command
4. Buat module:validate command

**Deliverables:**
- ModuleRegistry updated
- module:list command
- module:validate command

**Acceptance Criteria:**
- ModuleRegistry mendeteksi modul dengan struktur baru
- Validation berjalan untuk 8 layer
- Command menampilkan info modul

---

### Day 7: Testing & Documentation

**Tasks:**
1. Unit test untuk generator
2. Unit test untuk Shared Kernel
3. Documentation

**Deliverables:**
- Tests untuk MakeModuleCommand
- Tests untuk BaseModel
- Tests untuk BaseRepository
- Tests for ValueObjects

---

## Sprint 2: Console Core Modules (Week 2)

### Day 8-9: UserManagements

**Tasks:**
1. Generate modul dengan make:module
2. Buat migration users table (ULID)
3. Buat User model extends BaseModel
4. Buat UserRepository implements contract
5. Buat CreateUserAction
6. Buat UpdateUserAction
7. Buat DeleteUserAction
8. Buat ImpersonateUserAction
9. Buat UserController
10. Buat UserResource
11. Buat tests

**Deliverables:**
- UserManagements module complete
- Migration users table
- User CRUD actions
- User impersonation
- API endpoints
- Tests

**Acceptance Criteria:**
- User CRUD functional
- ULID digunakan untuk user id
- Impersonation working
- All tests pass

---

### Day 10-11: SystemSettings

**Tasks:**
1. Generate modul dengan make:module
2. Buat migration system_settings table
3. Buat Setting model
4. Buat Setting CRUD actions
5. Buat EmailSettings action
6. Buat BrandingSettings action
7. Buat MaintenanceMode action
8. Buat SystemSettingController
9. Buat tests

**Deliverables:**
- SystemSettings module complete
- Settings CRUD
- Email test functionality
- Branding settings
- Maintenance mode
- Tests

**Acceptance Criteria:**
- Settings CRUD functional
- Email test bisa kirim email
- Maintenance mode bisa di-toggle
- All tests pass

---

### Day 12-13: AuditLogs

**Tasks:**
1. Generate modul dengan make:module
2. Buat migration audit_logs table
3. Buat AuditLog model
4. Buat AuditObserver
5. Buat event listeners untuk semua modul
6. Buat AuditLogService untuk query
7. Buat AuditLogController
8. Buat tests

**Deliverables:**
- AuditLogs module complete
- Automatic audit trail
- Query service
- API endpoints
- Tests

**Acceptance Criteria:**
- Audit log tercatat untuk semua create/update/delete
- Query bisa filter by user, model, date
- All tests pass

---

### Day 14: Integration Testing & Polish

**Tasks:**
1. Integration test untuk semua modul
2. Cross-module test
3. Bug fix
4. Documentation

**Deliverables:**
- Integration tests
- Documentation
- Bug fixes

**Acceptance Criteria:**
- All 28+ tests pass
- No critical bugs
- Documentation complete

---

## Task Breakdown

### Foundation Tasks

| ID | Task | Estimate | Priority |
|---|---|---|---|
| F1 | Update MakeModuleCommand | 4h | P0 |
| F2 | Create ULID stubs | 2h | P0 |
| F3 | Create BaseModel | 2h | P0 |
| F4 | Create BaseRepository | 2h | P0 |
| F5 | Create ValueObjects | 3h | P0 |
| F6 | Update ModuleRegistry | 3h | P0 |
| F7 | Create module commands | 2h | P0 |
| F8 | Unit tests | 4h | P1 |

### UserManagements Tasks

| ID | Task | Estimate | Priority |
|---|---|---|---|
| U1 | Generate module structure | 1h | P0 |
| U2 | Create migration | 1h | P0 |
| U3 | Create User model | 1h | P0 |
| U4 | Create UserRepository | 2h | P0 |
| U5 | Create Actions (CRUD) | 4h | P0 |
| U6 | Create ImpersonateAction | 2h | P1 |
| U7 | Create Controller | 2h | P0 |
| U8 | Create Resource | 1h | P0 |
| U9 | Create tests | 4h | P1 |

### SystemSettings Tasks

| ID | Task | Estimate | Priority |
|---|---|---|---|
| S1 | Generate module structure | 1h | P0 |
| S2 | Create migration | 1h | P0 |
| S3 | Create Setting model | 1h | P0 |
| S4 | Create Actions | 4h | P0 |
| S5 | Create Controller | 2h | P0 |
| S6 | Create tests | 3h | P1 |

### AuditLogs Tasks

| ID | Task | Estimate | Priority |
|---|---|---|---|
| A1 | Generate module structure | 1h | P0 |
| A2 | Create migration | 1h | P0 |
| A3 | Create AuditLog model | 1h | P0 |
| A4 | Create AuditObserver | 3h | P0 |
| A5 | Create event listeners | 3h | P0 |
| A6 | Create AuditLogService | 2h | P0 |
| A7 | Create Controller | 2h | P0 |
| A8 | Create tests | 3h | P1 |

---

## Total Estimate

| Category | Hours |
|---|---|
| Foundation | 22h |
| UserManagements | 18h |
| SystemSettings | 13h |
| AuditLogs | 16h |
| **Total** | **69h** |

**Duration:** 2 weeks (80 hours capacity)
**Utilization:** 86%

---

## Risk Mitigation

| Risk | Mitigation |
|---|---|
| Generator complexity | Start with simple stubs, iterate |
| ULID migration issues | Test with fresh install first |
| Cross-module dependencies | Define contracts early |
| Time overrun | Focus on MVP, defer nice-to-have |

---

## Definition of Done

- [ ] All code follows DDD-Lite structure
- [ ] ULID digunakan untuk semua primary key
- [ ] All tests pass (minimum 85% coverage)
- [ ] No critical bugs
- [ ] Documentation updated
- [ ] Code reviewed
- [ ] Merged to dev branch
"