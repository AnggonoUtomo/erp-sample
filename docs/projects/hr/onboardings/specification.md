# Specification: HR Onboardings

## 1. Objective

Membangun module onboarding untuk HR Officer dan HR Manager agar proses masuk employee dapat dimulai dari checklist standar, dipantau per task, dan diselesaikan dengan histori yang dapat diaudit.

Success berarti:

- satu employment period tidak memiliki lebih dari satu onboarding aktif;
- task yang berlaku pada onboarding tidak berubah akibat edit template di kemudian hari;
- progress dan status dihitung secara deterministik;
- completion hanya berhasil ketika seluruh task wajib selesai;
- seluruh mutation dilindungi permission dan audit.

## 2. Refined product direction

### Problem statement

Bagaimana HR memastikan seluruh pekerjaan persiapan employee baru selesai tepat waktu tanpa bergantung pada spreadsheet, sekaligus mempertahankan histori checklist yang benar ketika template berubah?

### Opsi yang dievaluasi

1. Satu checklist statis dalam kode — sederhana tetapi tidak dapat disesuaikan HR.
2. Task langsung membaca template aktif — mudah dibangun tetapi histori berubah saat template diedit.
3. Template yang disalin menjadi task snapshot — sedikit lebih banyak data, tetapi histori stabil dan workflow mudah diaudit.
4. Workflow engine generik — fleksibel tetapi terlalu kompleks untuk satu use case MVP.

Pilihan: opsi 3. Lihat [ADR-001](decisions/001-checklist-driven-onboarding.md).

### Assumptions

- Employee sudah tersedia sebelum onboarding dimulai.
- Employment period diwakili contract bila contract tersedia; fallback MVP memakai employee + start date eksplisit sesuai [ADR-002](decisions/002-employment-fallback-and-calendar-due-date.md).
- HR Manager bertanggung jawab atas lifecycle case.
- Completion evidence MVP berupa catatan ringkas, actor, dan timestamp; file tetap melalui Employee Documents/DMS.

## 3. Functional requirements

### FR-01 — Checklist template

- HR berizin dapat membuat, melihat, memperbarui, mengarsipkan, dan merestore template.
- Template memiliki code unik, nama, deskripsi, status aktif, dan ordered items.
- Item memiliki title, description opsional, category, required flag, default due offset, dan default assignee role opsional.
- Template yang dipakai onboarding tidak boleh di-hard-delete.

### FR-02 — Start onboarding

- HR memilih employee, employment contract opsional, template aktif, start date, dan owner.
- Sistem memvalidasi employee aktif/tidak diarsipkan dan contract milik employee tersebut.
- Sistem membuat onboarding `DRAFT`, lalu menyalin item template menjadi task snapshot secara atomic.
- Idempotency/retry tidak boleh membuat case atau task duplikat.
- Retry identik dikenali dari fingerprint server-side; request berbeda untuk active employment identity yang sama ditolak.

### FR-03 — Activate onboarding

- Transition `DRAFT -> IN_PROGRESS` dilakukan eksplisit.
- Hanya satu onboarding `DRAFT` atau `IN_PROGRESS` diperbolehkan untuk employment period yang sama.
- Task required dan urutannya sudah terkunci sebagai snapshot saat activation.

### FR-04 — Task assignment dan completion

- Task dapat memiliki assignee user atau tetap unassigned.
- Assignee MVP adalah satu Console User non-deleted sesuai [ADR-003](decisions/003-console-user-assignment-and-completion-evidence.md); team/role virtual belum menjadi actor runtime.
- Task memiliki state `PENDING`, `IN_PROGRESS`, `COMPLETED`, atau `SKIPPED`.
- Task required tidak boleh di-skip tanpa permission dan reason khusus.
- Required-skip dan controlled reopen mengikuti [ADR-004](decisions/004-required-skip-and-controlled-reopen.md).
- Completion menyimpan actor, timestamp, dan note opsional.
- Reopen task menyimpan reason dan menghitung ulang progress.

### FR-05 — Progress

- Progress dihitung dari jumlah task terminal (`COMPLETED` atau valid `SKIPPED`) dibagi total task.
- Summary memisahkan required incomplete, optional incomplete, overdue, dan completed.
- Persentase memakai seluruh task terminal (`COMPLETED` atau valid `SKIPPED`), sedangkan completed count hanya menghitung status `COMPLETED`.
- Query tanggal memakai business date eksplisit untuk hasil deterministik.

### FR-06 — Complete/cancel onboarding

- `IN_PROGRESS -> COMPLETED` hanya jika seluruh required task terminal secara valid.
- `DRAFT|IN_PROGRESS -> CANCELLED` membutuhkan reason.
- Completed/cancelled onboarding tidak dapat diedit melalui generic update.
- Reopen onboarding tidak termasuk MVP.

### FR-07 — List dan detail

- List paginated dapat difilter employee, owner, state, template, start-date range, dan overdue.
- Detail menampilkan employee, employment context, progress, ordered tasks, actor, dan timestamp.
- Archived history tetap dapat dibaca sesuai permission.

### FR-08 — Authorization dan audit

- Permission minimum: view, template-manage, create, activate, task-update, complete, cancel, archive, restore, manage.
- Seluruh mutation diperiksa server-side.
- Audit mencatat actor, event, state before/after, dan identifier aman tanpa password/token/PII berlebih.

## 4. State contracts

### Onboarding

```txt
DRAFT -> IN_PROGRESS -> COMPLETED
   \          \
    ----------> CANCELLED
```

### Onboarding task

```txt
PENDING -> IN_PROGRESS -> COMPLETED
   \              \----> SKIPPED (policy + reason)
    <------------------- reopen -> PENDING
```

Invalid/repeated transition harus ditolak tanpa partial write. Retry completion yang sudah berhasil boleh idempotent tanpa audit ganda.

## 5. Data ownership dan relations

```txt
Employees 1 ---- * Onboardings
EmployeeContracts 0..1 ---- * Onboardings
OnboardingTemplates 1 ---- * OnboardingTemplateItems
Onboardings 1 ---- * OnboardingTasks (snapshot)
Console Users 0..1 ---- * OnboardingTasks (assignee)
```

Onboardings memiliki snapshot label penting dari template item; ia tidak membaca item template secara live untuk histori. File evidence tidak disimpan di module ini—gunakan Employee Documents/DMS reference.

## 6. Non-scope MVP

- onboarding portal employee/self-service;
- workflow/approval engine generik;
- email, WhatsApp, atau push reminder otomatis;
- asset inventory dan provisioning engine;
- e-signature;
- file storage atau media collection baru;
- probation review;
- offboarding;
- perubahan otomatis ke Attendance/Payroll;
- bulk import onboarding;
- reopen onboarding yang sudah completed/cancelled.

## 7. Proposed project structure

```txt
app/Modules/HR/Onboardings/
  Contracts/
  Database/Migrations/
  DTO/
  Enums/
  Http/Controllers/
  Http/Requests/
  Models/
  Policies/
  Providers/
  Services/
  Support/
  Transactions/
  module.php
  navigation.php
  permissions.php
  routes.php

resources/js/pages/hr/onboardings/
  index.tsx
  types.ts
  onboarding-components/
    onboarding-header.tsx
    onboarding-summary-cards.tsx
    onboarding-table.tsx
    onboarding-form.tsx
    onboarding-detail-card.tsx
    onboarding-task-list.tsx
    onboarding-lifecycle-dialog.tsx

tests/Feature/HR/
  OnboardingTemplateTest.php
  OnboardingTest.php
  OnboardingLifecycleTest.php
```

Nama test final mengikuti konvensi repository bila folder `tests/Feature/HR` belum dipakai.

## 8. Command design

Scaffold awal:

```bash
php artisan make:module Onboardings --project=HR
```

Command operasional read-only fase lanjut:

```bash
php artisan hr:onboardings:overdue --date=2026-07-15
```

Contract command:

- `--date=YYYY-MM-DD` wajib valid dan menjadi business date eksplisit;
- exit `0` jika query berhasil, termasuk hasil kosong;
- exit non-zero untuk input invalid;
- tidak mengirim notifikasi dan tidak mengubah database.

Quality commands:

```bash
php artisan module:validate
vendor/bin/pint --test
npm run lint:check
npm run format:check
npm run typecheck
npm run test:frontend
npm run build
php artisan test
git diff --check
```

## 9. Architecture and code rules

- Route memakai `/hr/onboardings` dan nama `hr.onboardings.*`.
- Write flow memakai FormRequest -> DTO -> Service -> Transaction.
- State transition tidak dilakukan dengan generic CRUD update.
- Tanggal bisnis disimpan sebagai date; audit timestamp tetap UTC.
- Soft delete berlaku pada template dan onboarding history; tidak ada force-delete MVP.
- Model Eloquent internal tidak menjadi integration contract.
- Frontend menggunakan typed props, komponen project, dan tidak menjadi security boundary.

## 10. Acceptance criteria

- [ ] Authorized HR dapat membuat template ordered checklist.
- [ ] Authorized HR dapat membuat draft onboarding dengan task snapshot atomic.
- [ ] Duplicate active onboarding untuk employment period yang sama ditolak.
- [ ] Edit template tidak mengubah task onboarding existing.
- [ ] Task assignment, complete, skip khusus, dan reopen menjaga audit serta progress.
- [x] Completion ditolak selama required task belum terminal secara sah.
- [x] Cancel membutuhkan reason dan tidak menghapus history.
- [x] List/filter/detail deterministic, paginated, dan permission-aware.
- [ ] Guest dan role tanpa permission ditolak pada seluruh mutation.
- [ ] Tidak ada storage engine, direct Payroll/Attendance dependency, atau hard delete.

## 11. Test plan

- Contract/module: manifest, dependency, permission, route, dan navigation valid.
- Template: uniqueness, ordering, archive/restore, used-template protection.
- Create: employee/contract validation, snapshot fidelity, atomic rollback, idempotency.
- Lifecycle: transition matrix, duplicate active guard, completion invariant, cancel reason.
- Task: assignment, required skip denial, completion/reopen audit, progress calculation.
- Authorization: global mutation route × permission denial matrix.
- Query: filters, overdue boundary, archived exclusion, explicit business date.
- Frontend: typed mapping, empty/error state, task controls, lifecycle button visibility.
- Regression: Employees, Employee Contracts, module validation, full backend/frontend gates.

## 12. Boundaries

### Always

- validasi input dan authorization server-side;
- transaction untuk create snapshot dan lifecycle mutation;
- audit state transition;
- update spec/ADR sebelum mengubah semantics.

### Ask first

- menambah dependency/library;
- menambah integration event publik;
- mengizinkan backdate, reopen completed, atau required-task skip;
- mengubah constraint satu active onboarding per employment period.

### Never

- menyimpan binary/file di Onboardings;
- hard delete histori onboarding;
- membaca internal model Payroll/Attendance;
- memperlakukan frontend visibility sebagai authorization;
- commit secret atau PII fixture nyata.

## 13. Open questions

- Employment fallback sudah diputuskan untuk MVP pada ADR-002; evaluasi ulang setelah contract diwajibkan oleh proses operasional.
- Assignee MVP sudah diputuskan hanya Console User pada ADR-003; team/role virtual dievaluasi setelah owner runtime tersedia.
- Optional task tidak di-skip otomatis saat onboarding completed; semantics terminal ditetapkan pada [ADR-005](decisions/005-terminal-lifecycle-evidence.md).
- Due date MVP memakai calendar day sesuai ADR-002; working-day calendar adalah evaluasi lanjutan.
- Approval completion MVP diwakili permission `onboardings.complete`; approval dua tahap dievaluasi setelah kebutuhan operasional nyata tersedia.
- Integration contract v1 tetap deferred menurut [ADR-007](decisions/007-defer-integration-contract-v1.md) sampai consumer nyata menyetujui schema dan delivery semantics.
