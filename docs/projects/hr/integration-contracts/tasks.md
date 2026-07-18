# Tasks: HR Integration Contracts

Semua task belum dikerjakan. Implementasi harus berurutan dan berhenti di checkpoint untuk review. Project ini adalah boundary teknis, bukan menu operasional.

## Task 01 — Module shell dan contract registry ✅

**Tujuan:** membuat shell module `HR/IntegrationContracts` dan registry contract v1 tanpa UI/menu user.

**Files yang disentuh:**

- `app/Modules/HR/IntegrationContracts/module.php`
- `app/Modules/HR/IntegrationContracts/permissions.php`
- `app/Modules/HR/IntegrationContracts/Providers/HRIntegrationContractsServiceProvider.php`
- `app/Modules/HR/IntegrationContracts/Support/HRIntegrationContractRegistry.php`
- `tests/Feature/HRIntegrationContractRegistryTest.php`
- `docs/projects/hr/integration-contracts/*`

**Acceptance criteria:**

- [x] Module valid di `php artisan module:validate`.
- [x] Registry mencatat contract snapshot dan event v1.
- [x] Tidak ada route/menu user baru.
- [x] Tidak ada migration/table baru.
- [x] Permission hanya untuk CLI/admin inspection bila diperlukan; tidak ada CRUD permission.

**Hasil implementasi:** selesai 2026-07-18. Module `HR/IntegrationContracts` terdaftar sebagai contract-only boundary dengan provider dan registry awal. Export route dan navigation dimatikan, permissions file sengaja kosong karena belum ada UI/route/command admin, dan tidak ada migration/table baru. Registry mencatat 4 snapshot contract v1 serta 10 event contract v1 sebagai daftar awal yang akan diisi DTO/provider pada task berikutnya.

**Test:**

```bash
php artisan test --filter=HRIntegrationContractRegistry
php artisan module:validate
```

**Dependencies:** specification dan ADR-001 approved. **Scope:** S.

## Task 02 — DTO dan forbidden-field privacy guard ✅

**Tujuan:** membuat DTO/envelope v1 dan guard yang memastikan payload integration tidak membawa field sensitif.

**Files yang disentuh:**

- `app/Modules/HR/IntegrationContracts/DTO/*`
- `app/Modules/HR/IntegrationContracts/Support/ForbiddenIntegrationFieldGuard.php`
- `tests/Feature/HRIntegrationEventPrivacyTest.php`

**Acceptance criteria:**

- [x] DTO memakai field allowlist eksplisit.
- [x] Event envelope memiliki `eventId`, `eventName`, `eventVersion`, `occurredAt`, `sourceModule`, `actorUserId`, `correlationId`, dan `payload`.
- [x] Guard menolak forbidden fields seperti password, token, document number, DMS URL/path, bank, salary, dan notes confidential.
- [x] Test privacy hijau.

**Hasil implementasi:** selesai 2026-07-18. DTO contract v1 tersedia untuk `EmployeeSnapshotV1`, `EmployeeAssignmentSnapshotV1`, `EmployeeContractSnapshotV1`, `EmployeeDocumentComplianceSnapshotV1`, dan `IntegrationEventEnvelopeV1`. Semua DTO memakai `toArray()` allowlist eksplisit. Assignment nested reference difilter agar hanya field operasional yang keluar. `ForbiddenIntegrationFieldGuard` memindai key payload secara rekursif dan `IntegrationEventEnvelopeV1` menolak payload yang membawa field sensitif sebelum event dibentuk.

**Test:**

```bash
php artisan test --filter=HRIntegrationEventPrivacy
```

**Dependencies:** Task 01. **Scope:** M.

## Task 03 — Employee snapshot provider

**Tujuan:** menyediakan `EmployeeSnapshotV1` read-only untuk identitas operasional minimal employee.

**Files yang disentuh:**

- `app/Modules/HR/IntegrationContracts/Contracts/EmployeeSnapshotProvider.php`
- `app/Modules/HR/IntegrationContracts/Services/EloquentEmployeeSnapshotProvider.php`
- `tests/Feature/HRIntegrationSnapshotTest.php`

**Acceptance criteria:**

- [ ] Provider menerima employee id dan menghasilkan snapshot minimal.
- [ ] Snapshot tidak memuat PII pribadi.
- [ ] Employee archived/tidak ditemukan menghasilkan null/not found semantics yang terdokumentasi.
- [ ] Provider tidak menulis database/audit/notification/queue/file.

**Test:**

```bash
php artisan test --filter=HRIntegrationSnapshot
```

**Dependencies:** Task 02 dan Employees. **Scope:** M.

## Task 04 — Assignment snapshot provider

**Tujuan:** menyediakan `EmployeeAssignmentSnapshotV1` berdasarkan tanggal acuan eksplisit.

**Files yang disentuh:**

- `app/Modules/HR/IntegrationContracts/Contracts/EmployeeAssignmentSnapshotProvider.php`
- `app/Modules/HR/IntegrationContracts/Services/EloquentEmployeeAssignmentSnapshotProvider.php`
- `tests/Feature/HRIntegrationAssignmentSnapshotTest.php`

**Acceptance criteria:**

- [ ] Provider menerima employee id dan `effectiveDate/asOf`.
- [ ] Snapshot memuat departement, position, job level, work location, employment status, dan employment type.
- [ ] Semantics terminal/inactive jelas untuk Attendance/Payroll.
- [ ] Output deterministic untuk tanggal acuan yang sama.

**Test:**

```bash
php artisan test --filter=HRIntegrationAssignmentSnapshot
```

**Dependencies:** Task 03, Employee Movements, master HR data. **Scope:** M.

## Checkpoint A — Read-only snapshot foundation

- [ ] Task 01–04 hijau.
- [ ] Tidak ada UI/menu/mutation baru.
- [ ] Forbidden-field guard membuktikan payload aman.
- [ ] Docs sesuai behavior aktual.

**Evidence yang wajib dicatat:**

```bash
vendor/bin/pint --test app/Modules/HR/IntegrationContracts tests/Feature/HRIntegration*
php artisan test --filter=HRIntegration
php artisan module:validate
git diff --check
```

## Task 05 — Contract dan document compliance snapshot

**Tujuan:** menyediakan snapshot ringkasan contract dan document compliance tanpa field sensitif.

**Files yang disentuh:**

- `app/Modules/HR/IntegrationContracts/Contracts/EmployeeContractSnapshotProvider.php`
- `app/Modules/HR/IntegrationContracts/Contracts/EmployeeDocumentComplianceSnapshotProvider.php`
- service/DTO terkait
- `tests/Feature/HRIntegrationComplianceSnapshotTest.php`

**Acceptance criteria:**

- [ ] Contract snapshot tidak memuat compensation, notes, atau attachment.
- [ ] Document compliance snapshot tidak memuat document number, DMS reference, storage path, URL, atau token.
- [ ] Snapshot menerima tanggal acuan eksplisit.
- [ ] Empty/missing data menghasilkan payload aman dan terdokumentasi.

**Test:**

```bash
php artisan test --filter=HRIntegrationComplianceSnapshot
```

**Dependencies:** Task 04, Employee Contracts, Employee Documents. **Scope:** M.

## Task 06 — Event envelope dan publisher mapping

**Tujuan:** mendefinisikan event v1 dan mapping source module tanpa listener downstream.

**Files yang disentuh:**

- `app/Modules/HR/IntegrationContracts/Events/*`
- `app/Modules/HR/IntegrationContracts/Support/HRIntegrationEventRegistry.php`
- optional source module publisher adapters
- `tests/Feature/HRIntegrationEventRegistryTest.php`

**Acceptance criteria:**

- [ ] Event v1 terdaftar di registry.
- [ ] Payload memakai envelope standar.
- [ ] Event tidak membawa forbidden fields.
- [ ] Tidak ada listener downstream Attendance/Payroll/CRM.
- [ ] Event hanya mewakili state yang sudah final/committed.

**Test:**

```bash
php artisan test --filter=HRIntegrationEventRegistry
```

**Dependencies:** Task 05. **Scope:** M.

## Task 07 — Read-only inspection commands

**Tujuan:** menyediakan command untuk melihat dan memvalidasi contract.

**Files yang disentuh:**

- `app/Modules/HR/IntegrationContracts/Console/Commands/DescribeHRIntegrationContractsCommand.php`
- `app/Modules/HR/IntegrationContracts/Console/Commands/ValidateHRIntegrationContractsCommand.php`
- `app/Modules/HR/IntegrationContracts/Console/Commands/SampleHRIntegrationContractsCommand.php`
- provider/module manifest
- `tests/Feature/HRIntegrationCommandTest.php`

**Acceptance criteria:**

- [ ] `describe` menampilkan contract dan versi.
- [ ] `validate` memeriksa registry dan forbidden fields.
- [ ] `sample` menghasilkan payload aman untuk employee tertentu.
- [ ] Semua command non-mutating.
- [ ] Input invalid menghasilkan exit code non-zero.

**Test:**

```bash
php artisan test --filter=HRIntegrationCommand
```

**Dependencies:** Task 06. **Scope:** M.

## Checkpoint B — Event contract ready

- [ ] Task 05–07 hijau.
- [ ] Event v1 siap dipakai consumer masa depan.
- [ ] Tidak ada route publik, listener downstream, queue/outbox, atau mutation spekulatif.
- [ ] Dokumentasi consumer boundary diperbarui.

**Evidence yang wajib dicatat:**

```bash
php artisan test --filter=HRIntegration
php artisan module:validate
vendor/bin/pint --test app/Modules/HR/IntegrationContracts tests/Feature/HRIntegration*
git diff --check
```

## Task 08 — Attendance dan Payroll consumer handoff docs

**Tujuan:** mendokumentasikan cara Attendance dan Payroll memakai contract HR tanpa query internal table.

**Files yang disentuh:**

- `docs/projects/hr/integration-contracts/consumer-handoff.md`
- `docs/planning/attendance.md`
- `docs/planning/payroll.md` jika tersedia
- `docs/projects/hr/roadmap.md`

**Acceptance criteria:**

- [ ] Attendance input contract tertulis jelas.
- [ ] Payroll input contract tertulis jelas.
- [ ] Field yang tidak boleh dipakai consumer disebutkan.
- [ ] Deferred items untuk queue/outbox/API publik dicatat.

**Test:**

```bash
php artisan module:validate
git diff --check
```

**Dependencies:** Checkpoint B. **Scope:** S.

## Final quality checkpoint

- [ ] Semua task MVP selesai.
- [ ] Full relevant quality gates hijau.
- [ ] README/spec/plan/tasks/ADR sesuai implementasi aktual.
- [ ] HR Integration Contracts siap menjadi fondasi Attendance/Payroll MVP.

**Evidence final:**

```bash
vendor/bin/pint --test
php artisan module:validate
npm run format:check
npm run lint:check
npm run typecheck
npm run build
php artisan test
git diff --check
```
