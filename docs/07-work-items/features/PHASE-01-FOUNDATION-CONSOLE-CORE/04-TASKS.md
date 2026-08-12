# Task Breakdown - Phase 1: Foundation + Console Core

## Fitur

- Fitur ID: PHASE-01-FOUNDATION-CONSOLE-CORE
- Spesifikasi status: proposed
- Perencanaan skill digunakan: planning-and-task-breakdown

## Dependensi Urutan

| Urutan | Task ID | Tujuan | Bergantung Pada | Status |
|---:|---|---|---|---|
| 1 | TSK-FND-GEN-01 | Update MakeModuleCommand | - | ready |
| 2 | TSK-FND-SHK-01 | Setup Shared Kernel | TSK-FND-GEN-01 | ready |
| 3 | TSK-FND-REG-01 | Update Module Registry | TSK-FND-SHK-01 | ready |
| 4 | TSK-CNS-USR-01 | UserManagements Module | TSK-FND-GEN-01, TSK-FND-SHK-01 | ready |
| 5 | TSK-CNS-SYS-01 | SystemSettings Module | TSK-FND-GEN-01, TSK-FND-SHK-01 | ready |
| 6 | TSK-CNS-AUD-01 | AuditLogs Module | TSK-FND-GEN-01, TSK-FND-SHK-01, TSK-CNS-USR-01 | ready |

---

## TSK-FND-GEN-01 - Update MakeModuleCommand

```yaml
status: ready
owner: unassigned
size: medium
references:
  - 01-FEATURE-SPEC.md
  - 02-TECHNICAL-DESIGN.md
```

### Tujuan

Update MakeModuleCommand untuk generate struktur DDD-Lite dengan 8 layer dan ULID support.

### Prakondisi

- File MakeModuleCommand existing ada di app/Support/Modules/Commands/
- Stub files struktur lama ada

### Diizinkan Perubahan

- app/Support/Modules/Commands/MakeModuleCommand.php
- Stub files di app/Support/Modules/Stubs/

### Dilarang/Tidak Terkait Perubahan

- Modul existing tidak boleh diubah
- Frontend tidak boleh diubah

### Implementasi Catatan

1. Analisis struktur existing MakeModuleCommand
2. Buat stub files untuk 8 layer DDD-Lite
3. Tambahkan ULID support di migration stub
4. Test dengan modul dummy

### Kriteria Penerimaan

- [ ] php artisan make:module Console/TestModule generate struktur lengkap
- [ ] 8 layer ter-generate: Application, Domain, Infrastructure, Presentation, Integration, Database, Routes, Tests
- [ ] ULID digunakan untuk primary key di migration
- [ ] ServiceProvider ter-register otomatis
- [ ] Routes file ter-load

### Wajib Pengujian

- [ ] Unit test untuk generator
- [ ] Manual test generate modul

### Verifikasi Perintah

```bash
php artisan make:module Console/TestModule
php artisan module:list
```

### Penyelesaian Bukti

- File diubah: MakeModuleCommand.php, stub files
- Perintah/hasil: output artisan command
- Deviasi: (catat jika ada)
- Risiko/limitations: (catat jika ada)
- Dokumentasi diperbarui: (ya/tidak)

---

## TSK-FND-SHK-01 - Setup Shared Kernel

```yaml
status: ready
owner: unassigned
size: medium
references:
  - 01-FEATURE-SPEC.md
  - 02-TECHNICAL-DESIGN.md
```

### Tujuan

Setup Shared Kernel: BaseModel, BaseRepository, ValueObjects, BaseAction, BaseEvent.

### Prakondisi

- MakeModuleCommand sudah diupdate (TSK-FND-GEN-01 completed)

### Diizinkan Perubahan

- app/Shared/Models/BaseModel.php
- app/Shared/Repositories/BaseRepository.php
- app/Shared/ValueObjects/Money.php
- app/Shared/ValueObjects/DateRange.php
- app/Shared/ValueObjects/Status.php
- app/Shared/Actions/BaseAction.php
- app/Shared/Events/BaseEvent.php

### Dilarang/Tidak Terkait Perubahan

- Modul existing tidak boleh diubah

### Implementasi Catatan

1. Buat BaseModel dengan HasUlids trait
2. Buat BaseRepository dengan CRUD methods
3. Buat ValueObjects: Money, DateRange, Status
4. Buat BaseAction abstract class
5. Buat BaseEvent abstract class

### Kriteria Penerimaan

- [ ] BaseModel bisa digunakan untuk create model dengan ULID
- [ ] BaseRepository bisa CRUD
- [ ] ValueObjects immutable dan readonly
- [ ] BaseAction punya method handle()
- [ ] BaseEvent bisa dispatch

### Wajib Pengujian

- [ ] Unit test untuk BaseModel
- [ ] Unit test untuk BaseRepository
- [ ] Unit test untuk ValueObjects

### Verifikasi Perintah

```bash
php artisan tinker
>>> $model = new TestModel();
>>> $model->id; // should be ULID
```

### Penyelesaian Bukti

- File diubah: 7 files di app/Shared/
- Perintah/hasil: output tinker
- Deviasi: (catat jika ada)
- Risiko/limitations: (catat jika ada)
- Dokumentasi diperbarui: (ya/tidak)

---

## TSK-FND-REG-01 - Update Module Registry

```yaml
status: ready
owner: unassigned
size: small
references:
  - 01-FEATURE-SPEC.md
  - 02-TECHNICAL-DESIGN.md
```

### Tujuan

Update ModuleRegistry untuk mendeteksi modul dengan struktur DDD-Lite dan tambahkan validation.

### Prakondisi

- Shared Kernel sudah setup (TSK-FND-SHK-01 completed)

### Diizinkan Perubahan

- app/Support/Modules/ModuleRegistry.php
- app/Support/Modules/ModuleContractValidator.php

### Dilarang/Tidak Terkait Perubahan

- Modul existing tidak boleh diubah

### Implementasi Catatan

1. Update ModuleRegistry untuk struktur baru
2. Tambahkan module validation
3. Buat module:list command
4. Buat module:validate command

### Kriteria Penerimaan

- [ ] ModuleRegistry mendeteksi modul dengan struktur baru
- [ ] Validation berjalan untuk 8 layer
- [ ] module:list command menampilkan info lengkap
- [ ] module:validate command berjalan

### Wajib Pengujian

- [ ] Unit test untuk ModuleRegistry
- [ ] Manual test commands

### Verifikasi Perintah

```bash
php artisan module:list
php artisan module:validate Console/TestModule
```

### Penyelesaian Bukti

- File diubah: ModuleRegistry.php, ModuleContractValidator.php
- Perintah/hasil: output commands
- Deviasi: (catat jika ada)
- Risiko/limitations: (catat jika ada)
- Dokumentasi diperbarui: (ya/tidak)

---

## TSK-CNS-USR-01 - UserManagements Module

```yaml
status: ready
owner: unassigned
size: large
references:
  - 01-FEATURE-SPEC.md
  - 02-TECHNICAL-DESIGN.md
```

### Tujuan

Konversi modul UserManagements ke struktur DDD-Lite dengan ULID.

### Prakondisi

- MakeModuleCommand sudah diupdate (TSK-FND-GEN-01 completed)
- Shared Kernel sudah setup (TSK-FND-SHK-01 completed)

### Diizinkan Perubahan

- app/Modules/Console/UserManagements/ (seluruh struktur)
- Database migration users table

### Dilarang/Tidak Terkait Perubahan

- Modul lain tidak boleh diubah
- Frontend tidak boleh diubah

### Implementasi Catatan

1. Generate struktur dengan make:module
2. Buat migration users table dengan ULID
3. Buat User model extends BaseModel
4. Buat UserRepository implements contract
5. Buat Actions: CreateUser, UpdateUser, DeleteUser, ImpersonateUser
6. Buat UserController dengan API endpoints
7. Buat UserResource untuk response
8. Buat tests

### Kriteria Penerimaan

- [ ] User CRUD functional dengan ULID
- [ ] User impersonation working
- [ ] API endpoints accessible
- [ ] All tests pass

### Wajib Pengujian

- [ ] Unit tests untuk Actions
- [ ] Feature tests untuk Controller
- [ ] Integration tests untuk Repository

### Verifikasi Perintah

```bash
php artisan test --filter=UserManagements
php artisan migrate:fresh --seed
```

### Penyelesaian Bukti

- File diubah: seluruh struktur UserManagements
- Perintah/hasil: output tests
- Deviasi: (catat jika ada)
- Risiko/limitations: (catat jika ada)
- Dokumentasi diperbarui: (ya/tidak)

---

## TSK-CNS-SYS-01 - SystemSettings Module

```yaml
status: ready
owner: unassigned
size: large
references:
  - 01-FEATURE-SPEC.md
  - 02-TECHNICAL-DESIGN.md
```

### Tujuan

Konversi modul SystemSettings ke struktur DDD-Lite dengan ULID.

### Prakondisi

- MakeModuleCommand sudah diupdate (TSK-FND-GEN-01 completed)
- Shared Kernel sudah setup (TSK-FND-SHK-01 completed)

### Diizinkan Perubahan

- app/Modules/Console/SystemSettings/ (seluruh struktur)
- Database migration system_settings table

### Dilarang/Tidak Terkait Perubahan

- Modul lain tidak boleh diubah

### Implementasi Catatan

1. Generate struktur dengan make:module
2. Buat migration system_settings table
3. Buat Setting model
4. Buat Actions untuk CRUD
5. Buat EmailSettings action dengan test email
6. Buat BrandingSettings action
7. Buat MaintenanceMode action
8. Buat SystemSettingController
9. Buat tests

### Kriteria Penerimaan

- [ ] Settings CRUD functional
- [ ] Email test bisa kirim email
- [ ] Maintenance mode bisa di-toggle
- [ ] All tests pass

### Wajib Pengujian

- [ ] Unit tests untuk Actions
- [ ] Feature tests untuk Controller

### Verifikasi Perintah

```bash
php artisan test --filter=SystemSettings
```

### Penyelesaian Bukti

- File diubah: seluruh struktur SystemSettings
- Perintah/hasil: output tests
- Deviasi: (catat jika ada)
- Risiko/limitations: (catat jika ada)
- Dokumentasi diperbarui: (ya/tidak)

---

## TSK-CNS-AUD-01 - AuditLogs Module

```yaml
status: ready
owner: unassigned
size: large
references:
  - 01-FEATURE-SPEC.md
  - 02-TECHNICAL-DESIGN.md
```

### Tujuan

Konversi modul AuditLogs ke struktur DDD-Lite dengan ULID dan automatic audit trail.

### Prakondisi

- MakeModuleCommand sudah diupdate (TSK-FND-GEN-01 completed)
- Shared Kernel sudah setup (TSK-FND-SHK-01 completed)
- UserManagements completed (TSK-CNS-USR-01 completed)

### Diizinkan Perubahan

- app/Modules/Console/AuditLogs/ (seluruh struktur)
- Database migration audit_logs table

### Dilarang/Tidak Terkait Perubahan

- Modul lain tidak boleh diubah secara langsung

### Implementasi Catatan

1. Generate struktur dengan make:module
2. Buat migration audit_logs table
3. Buat AuditLog model
4. Buat AuditObserver untuk automatic logging
5. Buat event listeners untuk semua modul
6. Buat AuditLogService untuk query
7. Buat AuditLogController
8. Buat tests

### Kriteria Penerimaan

- [ ] Audit log tercatat untuk semua create/update/delete
- [ ] Query bisa filter by user, model, date
- [ ] All tests pass

### Wajib Pengujian

- [ ] Unit tests untuk Observer
- [ ] Feature tests untuk Controller
- [ ] Integration tests untuk automatic logging

### Verifikasi Perintah

```bash
php artisan test --filter=AuditLogs
```

### Penyelesaian Bukti

- File diubah: seluruh struktur AuditLogs
- Perintah/hasil: output tests
- Deviasi: (catat jika ada)
- Risiko/limitations: (catat jika ada)
- Dokumentasi diperbarui: (ya/tidak)
