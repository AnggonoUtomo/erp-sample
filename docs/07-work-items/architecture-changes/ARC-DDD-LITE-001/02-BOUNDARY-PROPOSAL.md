# 02 Proposal Boundary

> Fokus wajib: kapabilitas, tanggung jawab, module turunan, kontrak, ownership, dan alternatif.

## Metadata

```yaml
work_item: ARC-DDD-LITE-001
status: draft
owner: unassigned
last_updated: 2026-08-12
```

## Tujuan

Mengusulkan boundary baru untuk setiap modul dengan struktur DDD-Lite layered. Boundary ini mendefinisikan tanggung jawab setiap layer dan aturan dependency.

## Input dan Referensi

- docs/DDD-Lite-Modular-Monolith-Laravel-Acuan.md - Acuan arsitektur
- docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/01-DISCOVERY-RECORD.md - Catatan penemuan
- app/Modules/ - Struktur saat ini

## Detail

### Boundary Definition: Layer DDD-Lite

#### 1. Application Layer

**Tanggung Jawab:**
- Mengorkestrasi use case aplikasi
- Memanggil Domain Logic atau Contract modul lain
- Mengelola transaksi database
- Menghasilkan atau dispatch event
- Mengembalikan hasil use case

**Folder:**
- Application/Actions/ - Use case actions
- Application/DTO/ - Data Transfer Objects
- Application/Queries/ - CQRS queries
- Application/Services/ - Application services
- Application/Contracts/ - Interface contracts

**Aturan:**
- Tidak boleh bergantung pada detail HTTP
- Satu Action = satu use case
- Transaksi database di level Application Action

#### 2. Domain Layer

**Tanggung Jawab:**
- Aturan bisnis inti
- Entity dan Value Object
- Domain Event
- Domain Exception
- Domain Service

**Folder:**
- Domain/Contracts/ - Domain interfaces
- Domain/Entities/ - Domain entities
- Domain/Events/ - Domain events
- Domain/Exceptions/ - Domain exceptions
- Domain/Services/ - Domain services
- Domain/ValueObjects/ - Value objects

**Aturan:**
- Domain tidak bergantung pada Controller, Request, Inertia, atau View
- Value Object hanya dibuat jika memiliki manfaat nyata

#### 3. Infrastructure Layer

**Tanggung Jawab:**
- Detail teknis (Eloquent, repository, observer, external integration)
- Implementasi kontrak
- Provider dan binding

**Folder:**
- Infrastructure/Persistence/Models/ - Eloquent Models
- Infrastructure/Persistence/Repositories/ - Repository implementations
- Infrastructure/Observers/ - Eloquent observers
- Infrastructure/Providers/ - Infrastructure providers
- Infrastructure/External/ - External integrations

**Aturan:**
- Infrastructure boleh bergantung pada framework Laravel
- Model Eloquent di Infrastructure, bukan Domain

#### 4. Presentation Layer

**Tanggung Jawab:**
- Interaksi dengan dunia luar (HTTP, CLI)
- Validasi request
- Response formatting
- Authorization policies

**Folder:**
- Presentation/Controllers/ - HTTP controllers
- Presentation/Policies/ - Authorization policies
- Presentation/Requests/ - Form requests
- Presentation/Resources/ - API resources

**Aturan:**
- Controller hanya menerima dan mengembalikan HTTP
- Controller memanggil Application Action atau Query

#### 5. Integration Layer

**Folder:**
- Integration/Contracts/ - Interface contracts
- Integration/Listeners/ - Event listeners
- Integration/Services/ - Integration services

#### 6. Database Layer

**Folder:**
- Database/Factories/
- Database/Migrations/
- Database/Seeders/

#### 7. Routes

**Folder:**
- Routes/web.php
- Routes/api.php
- Routes/console.php
- Routes/channels.php

#### 8. Tests

**Folder:**
- Tests/Feature/
- Tests/Integration/
- Tests/Unit/

### Mapping: Flat ke Layered

| Folder Flat Saat Ini | Folder Layered Target |
|---|---|
| DTO/ | Application/DTO/ |
| Events/ | Domain/Events/ atau Integration/Events/ |
| Http/Controllers/ | Presentation/Controllers/ |
| Http/Requests/ | Presentation/Requests/ |
| Integrations/ | Integration/ |
| Listeners/ | Integration/Listeners/ |
| Models/ | Infrastructure/Persistence/Models/ |
| Policies/ | Presentation/Policies/ |
| Providers/ | Infrastructure/Providers/ |
| Services/ | Application/Services/ atau Domain/Services/ |
| Support/ | Domain/ atau di-root modul |
| Transactions/ | Application/ |
| Database/ | Database/ (tetap) |
| Console/ | Application/Console/ |
| Enums/ | Domain/Enums/ atau Shared/Enums/ |
| module.php | Root modul (tetap) |
| routes.php | Routes/web.php |
| permissions.php | Root modul (tetap) |
| navigation.php | Root modul (tetap) |

### Module Turunan yang Diusulkan

Setiap modul HR menjadi boundary turunan dari HR boundary.

### Kontrak Publik yang Diusulkan

Setiap modul mengekspos kontrak melalui Application/Contracts/.

### Ownership Data

| Modul | Tabel Utama | Owner |
|---|---|---|
| HR/WorkLocations | hr_work_locations | WorkLocations |
| HR/Positions | hr_positions | Positions |
| HR/OrganizationStructures | hr_organization_structures | OrganizationStructures |
| HR/Employees | hr_employees | Employees |
| HR/EmployeeContracts | hr_employee_contracts | EmployeeContracts |
| HR/EmployeeDocuments | hr_employee_documents | EmployeeDocuments |
| HR/EmployeeMovements | hr_employee_movements | EmployeeMovements |
| HR/EmploymentStatuses | hr_employment_statuses | EmploymentStatuses |
| HR/EmploymentTypes | hr_employment_types | EmploymentTypes |
| HR/JobLevels | hr_job_levels | JobLevels |
| HR/Departements | hr_departements | Departements |
| HR/Onboardings | hr_onboardings, hr_onboarding_tasks | Onboardings |
| HR/Offboardings | hr_offboardings, hr_offboarding_tasks | Offboardings |
| DocumentManagement | documents, document_versions | DocumentManagement |

## Keputusan / Hasil

1. 4 layer utama: Application, Domain, Infrastructure, Presentation
2. Folder tambahan: Integration, Database, Routes, Tests
3. Model Eloquent pindah ke Infrastructure/Persistence/Models/
4. Services dipisah: Application services dan Domain services
5. Events dipisah: Domain events dan Integration events

## Risiko dan Pertanyaan Terbuka

| Risiko | Dampak | Mitigasi |
|---|---|---|
| Terlalu banyak layer untuk modul sederhana | Over-engineering | Gunakan struktur minimal |
| Kebingungan penempatan Domain vs Application services | Medium | Dokumentasikan kriteria |
| Namespace changes yang luas | High | Incremental migration |

## Persetujuan yang Diperlukan

- [ ] Human Decision Gate untuk boundary proposal
- [ ] Approval untuk struktur layer
- [ ] Approval untuk mapping flat ke layered

## Keterlacakan

- Terkait dengan docs/DDD-Lite-Modular-Monolith-Laravel-Acuan.md Section 4
- Terkait dengan docs/03-architecture/MODULE-CATALOG.md
