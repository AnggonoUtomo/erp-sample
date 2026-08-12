# Implementation Plan

## Overview

Rencana implementasi ERP system dengan arsitektur DDD-Lite Modular Monolith.

## Total Modules: 28

| Boundary | Modules | Description |
|---|---|---|
| Console | 11 | System administration (PRIORITAS) |
| HR | 16 | Core HR management |
| DocumentManagement | 1 | Document management |

## Timeline: 2 Bulan (8 Minggu)

### Phase 1: Foundation + Console Core (Weeks 1-2)

**Tujuan:** Setup foundation dan konversi modul Console core.

**Tasks:**
1. Update MakeModuleCommand untuk DDD-Lite structure + ULID
2. Setup Shared Kernel (ValueObjects, base classes)
3. Setup module registry dan validation
4. Console/UserManagements - User management (PRIORITAS)
5. Console/SystemSettings - System configuration (PRIORITAS)
6. Console/AuditLogs - Audit trail (PRIORITAS)

**Deliverables:**
- Module generator dengan struktur DDD-Lite + ULID
- Shared Kernel functional
- 3 modul Console core dengan struktur DDD-Lite
- Kontrak publik untuk setiap modul

**Success Criteria:**
- php artisan make:module generate struktur DDD-Lite
- ULID digunakan untuk semua primary key
- All modules follow DDD-Lite structure
- Contracts defined and bound
- All tests pass

---

### Phase 2: Console Advanced (Weeks 3-4)

**Tujuan:** Konversi modul Console lanjutan.

**Tasks:**
1. Console/BackupRestores - Database backup
2. Console/AccessControls - Access control
3. Console/ActivityCenters - Activity tracking
4. Console/GlobalSearches - Cross-module search
5. Console/LoginActivities - Login monitoring
6. Console/NotificationTemplates - Notification templates
7. Console/QueueMonitors - Queue monitoring
8. Console/SchedulerMonitors - Scheduler monitoring

**Deliverables:**
- 8 modul Console advanced dengan struktur DDD-Lite
- Semua modul Console selesai

**Success Criteria:**
- All 11 Console modules functional
- Cross-module integration working
- All tests pass

---

### Phase 3: Core HR (Weeks 5-6)

**Tujuan:** Konversi modul HR core.

**Tasks:**
1. HR/WorkLocations - Work location management
2. HR/Positions - Position management
3. HR/OrganizationStructures - Org structure
4. HR/Employees - Employee management
5. HR/EmploymentTypes - Employment type
6. HR/EmploymentStatuses - Employment status
7. HR/JobLevels - Job level
8. HR/Departements - Department

**Deliverables:**
- 8 modul HR core dengan struktur DDD-Lite

**Success Criteria:**
- All 8 HR core modules functional
- Contracts defined and bound
- All tests pass

---

### Phase 4: Employee Lifecycle + Integration (Weeks 7-8)

**Tujuan:** Employee lifecycle, Document Management, dan integration.

**Tasks:**
1. HR/Onboardings - Onboarding workflow
2. HR/Offboardings - Offboarding workflow
3. HR/EmployeeMovements - Employee movement
4. HR/EmployeeContracts - Contract management
5. HR/EmployeeDocuments - Document tracking
6. HR/IntegrationContracts - Cross-module contracts
7. HR/HRReferenceData - Reference data
8. HR/HRReports - HR reporting
9. DocumentManagement - Document CRUD, versioning, approval

**Deliverables:**
- Semua modul HR selesai
- DocumentManagement selesai
- Integration contracts functional

**Success Criteria:**
- All 28 modules functional
- Integration contracts working
- All tests pass
- Production ready

---

## Timeline Summary

```
Week 1-2:  Phase 1 - Foundation + Console Core (3 Console modules)
Week 3-4:  Phase 2 - Console Advanced (8 Console modules)
Week 5-6:  Phase 3 - Core HR (8 HR modules)
Week 7-8:  Phase 4 - Lifecycle + Integration (9 modules)
```

## Module Priority Matrix

| Priority | Modules | Phase |
|---|---|---|
| P0 (Foundation) | Generator, Shared Kernel | Phase 1 |
| P1 (Console Core) | UserManagements, SystemSettings, AuditLogs | Phase 1 |
| P2 (Console Advanced) | BackupRestores, AccessControls, ActivityCenters, GlobalSearches, LoginActivities, NotificationTemplates, QueueMonitors, SchedulerMonitors | Phase 2 |
| P3 (Core HR) | WorkLocations, Positions, OrgStructures, Employees, EmpTypes, EmpStatuses, JobLevels, Depts | Phase 3 |
| P4 (Lifecycle) | Onboardings, Offboardings, Movements, Contracts, Documents, IntegrationContracts, HRReferenceData, HRReports, DocumentManagement | Phase 4 |

## Dependencies

```
Phase 1 (Generator + Console Core)
    |
    v
Phase 2 (Console Advanced)
    |
    v
Phase 3 (Core HR)
    |
    v
Phase 4 (Lifecycle + Integration)
```

## Risk Management

| Risiko | Dampak | Probabilitas | Mitigasi |
|---|---|---|---|
| Timeline 2 bulan sangat ketat | High | High | Parallel development, fokus MVP |
| 28 modul dalam 8 minggu | High | High | Sprint 2 minggu, daily standup |
| Kompleksitas DDD | Medium | Medium | Template generator, pair programming |
| ULID migration | Medium | Low | Generate ULID di model boot |

## Resource Allocation

| Role | Count | Allocation |
|---|---|---|
| Backend Developer | 3-4 | Full time |
| Frontend Developer | 2 | Full time |
| DevOps Engineer | 1 | Part time |
| QA Engineer | 2 | Full time |

## Quality Gates

| Gate | Criteria |
|---|---|
| Phase 1 Complete | Generator works, 3 Console modules functional, ULID working |
| Phase 2 Complete | All 11 Console modules functional |
| Phase 3 Complete | 8 HR core modules functional |
| Phase 4 Complete | All 28 modules functional, production ready |
