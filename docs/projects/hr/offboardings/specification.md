# Specification: HR Offboardings

## 1. Objective

Membangun module Offboardings agar HR dapat merencanakan separation employee, menjalankan checklist handover, dan menerapkan employment exit secara effective-dated, atomic, permission-aware, serta dapat diaudit.

Success berarti:

- satu employment identity tidak memiliki lebih dari satu offboarding aktif;
- task existing tidak berubah ketika template diedit;
- checklist readiness terpisah dari employment termination;
- finalization hanya terjadi pada/ setelah exit date dan tepat satu kali;
- Employee, Contract, dan Offboarding tidak berakhir pada state yang bertentangan;
- tidak ada direct dependency ke Attendance/Payroll atau storage engine baru.

## 2. Product direction

Pilihan produk adalah checklist snapshot yang diikuti atomic effective exit. Lihat [idea refinement](00-idea-refinement.md), [ADR-001](decisions/001-checklist-snapshot-and-exit-readiness.md), dan [ADR-002](decisions/002-effective-dated-termination-boundary.md).

### Assumptions

- Employee aktif dan tidak diarsipkan saat draft dibuat.
- Active/effective Employee Contract digunakan bila tersedia; fallback memakai Employee + employment start context.
- Exit status menunjuk Employment Status aktif dengan `is_final_status=true`.
- Exit evidence file tetap menjadi Employee Documents/DMS reference.
- Instruksi 2026-07-16 menyetujui specification/ADR dan memulai vertical slice pertama.

## 3. Functional requirements

### FR-01 — Checklist template

- Authorized HR dapat create, list, update, archive, dan restore template.
- Template memiliki code unik, name, description, active flag, dan ordered items.
- Item memiliki title, description opsional, category, required flag, due offset relatif terhadap exit date, dan default assignee role opsional.
- Template yang pernah dipakai tidak di-hard-delete.

### FR-02 — Draft offboarding

- HR memilih employee, contract opsional, template aktif, exit date, exit type, exit reason, owner, dan notes opsional.
- Contract wajib milik employee dan harus relevan terhadap employment period.
- Sistem membuat `DRAFT` dan menyalin template items menjadi task snapshot secara atomic.
- Due date task dihitung deterministic dari exit date dan signed offset.
- Retry identik idempotent; request berbeda untuk active employment identity yang sama ditolak.

### FR-03 — Activation

- `DRAFT -> IN_PROGRESS` dilakukan eksplisit.
- Activation mengunci snapshot penting dan memastikan employee belum exited/archived.
- Hanya satu `DRAFT`, `IN_PROGRESS`, atau `READY_FOR_EXIT` per employment identity.

### FR-04 — Task lifecycle

- Task state: `PENDING`, `IN_PROGRESS`, `COMPLETED`, `SKIPPED`.
- Task dapat di-assign ke satu Console User non-deleted.
- Required skip memerlukan permission khusus dan reason.
- Complete/skip/reopen menyimpan actor, timestamp, dan reason/note.
- File evidence hanya berupa reference Employee Documents/DMS, bukan binary baru.

### FR-05 — Progress dan readiness

- Progress menghitung task terminal dibanding total task.
- Summary memisahkan required incomplete, optional incomplete, completed, skipped, dan overdue.
- `IN_PROGRESS -> READY_FOR_EXIT` hanya jika seluruh required task terminal secara sah.
- Reopen task pada case ready mengembalikan case ke `IN_PROGRESS` secara atomic.

### FR-06 — Effective finalization

- `READY_FOR_EXIT -> COMPLETED` hanya pada/ setelah exit date terhadap business date eksplisit.
- Finalization mengunci Offboarding, Employee, dan relevant Contract dalam urutan konsisten.
- Employee mendapat `ended_at=exit_date`, `active=false`, serta employment status final yang dipilih dan tervalidasi.
- Relevant active Contract diakhiri pada exit date dengan reason terkontrol.
- Retry setelah sukses idempotent dan tidak membuat audit/event ganda.
- Partial failure me-rollback seluruh perubahan.

### FR-07 — Cancel dan terminal state

- `DRAFT|IN_PROGRESS|READY_FOR_EXIT -> CANCELLED` membutuhkan reason.
- `COMPLETED` dan `CANCELLED` immutable.
- Reopen terminal offboarding tidak termasuk MVP.

### FR-08 — List, archive, dan command

- List paginated mendukung employee, owner, status, template, exit type, exit-date range, due/overdue, business date, dan archived.
- Hanya terminal offboarding dapat di-archive/restore.
- Tidak ada force delete.
- Command due/overdue bersifat read-only dan memakai business date eksplisit.

### FR-09 — Authorization dan audit

- Permission minimum: view, template-manage, create, activate, task-update, task-skip-required, mark-ready, finalize, cancel, archive, restore, manage.
- Seluruh mutation diperiksa server-side.
- Audit mencatat actor, event, state before/after, reason, dan identifier aman tanpa PII/secret.

## 4. State contracts

### Offboarding

```txt
DRAFT -> IN_PROGRESS -> READY_FOR_EXIT -> COMPLETED
   \          \               \
    ----------------------------------> CANCELLED
```

Transition lain fail-closed. `COMPLETED` dan `CANCELLED` terminal.

### Offboarding task

```txt
PENDING -> IN_PROGRESS -> COMPLETED
   \              \----> SKIPPED (policy + reason)
    <------------------- reopen -> PENDING
```

## 5. Data ownership dan relations

```txt
Employees 1 ---- * Offboardings
EmployeeContracts 0..1 ---- * Offboardings
EmploymentStatuses 1 ---- * Offboardings (target final status)
OffboardingTemplates 1 ---- * OffboardingTemplateItems
Offboardings 1 ---- * OffboardingTasks (snapshot)
Console Users 0..1 ---- * OffboardingTasks (assignee)
```

Offboardings owner atas process state, checklist, exit intent, dan completion evidence. Employees owner atas employee profile; Employee Contracts owner atas contract lifecycle.

## 6. Non-scope MVP

- offboarding self-service portal;
- approval engine multi-level;
- automatic account disable/revoke;
- asset inventory atau financial settlement engine;
- exit interview survey builder;
- Attendance/Payroll mutation langsung;
- public integration event tanpa approved consumer;
- email/WhatsApp reminder;
- file storage baru;
- bulk import;
- reopen terminal case.

## 7. Project structure

```txt
app/Modules/HR/Offboardings/
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

resources/js/pages/hr/offboardings/
  index.tsx
  show.tsx
  types.ts
  offboarding-components/

tests/Feature/
  OffboardingFoundationTest.php
  OffboardingTemplateTest.php
  OffboardingDraftSnapshotTest.php
  OffboardingLifecycleTest.php
  OffboardingFinalizationTest.php
  OffboardingAuthorizationMatrixTest.php
```

## 8. Command design

Scaffold reference:

```bash
php artisan make:module Offboardings --project=HR
```

Read-only operational command:

```bash
php artisan hr:offboardings:due --date=2026-07-16 --within=30
```

Contract:

- `--date=YYYY-MM-DD` wajib valid sebagai business date;
- `--within=0..365` membatasi window;
- exit `0` untuk query berhasil termasuk kosong;
- exit non-zero untuk input invalid;
- tidak mengubah database dan tidak mengirim notification.

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

## 9. Architecture rules

- Route memakai `/hr/offboardings` dan nama `hr.offboardings.*`.
- Write flow: FormRequest -> DTO -> Service -> Transaction.
- State transition tidak memakai generic status update.
- Business dates disimpan sebagai `date`; audit timestamp UTC.
- Cross-module mutation memakai interface/adapter resmi, bukan import service/model internal.
- Lock order finalization tetap: Offboarding -> Employee -> Contract -> Tasks.
- Frontend typed props bukan security boundary.
- Event public hanya `EmployeeOffboardingCompletedV1` setelah gate disetujui; listener downstream tetap kosong sampai consumer mengimplementasikan handler sendiri.

## 10. Acceptance criteria

- [ ] Module/state/permission boundary tervalidasi tanpa mutation route prematur.
- [ ] Authorized HR dapat membuat template ordered checklist.
- [ ] Draft dan task snapshot terbentuk atomic serta idempotent.
- [ ] Duplicate active employment identity ditolak.
- [ ] Assignment, completion, controlled skip, dan reopen menjaga audit/progress.
- [ ] Ready ditolak selama required task incomplete.
- [ ] Finalization sebelum exit date ditolak.
- [ ] Finalization mengubah Offboarding, Employee, dan Contract secara atomic tepat satu kali.
- [ ] Cancel, archive, restore, filters, dan due command deterministic.
- [ ] Guest dan unauthorized role ditolak pada seluruh mutation.
- [ ] Tidak ada hard delete, storage engine, secret/PII leak, atau direct Attendance/Payroll dependency.

## 11. Test plan

- Foundation: manifest, dependencies, permission, enums, route/navigation disabled.
- Template: uniqueness, ordering, snapshot source, archive/restore.
- Draft: employee/contract/status validation, snapshot fidelity, due date, rollback, idempotency.
- Lifecycle: transition matrix, duplicate guard, ready invariant, cancel reason.
- Task: assignment, required skip denial, completion/reopen, ready rollback.
- Finalization: business date, lock/idempotency, Employee/Contract update, rollback injection.
- Authorization: global mutation route × permission denial matrix.
- Query: filters, due/overdue boundaries, archived scope, pagination.
- Frontend: typed mapping, empty/error state, accessible lifecycle controls.
- Regression: Employees, Employee Contracts, Onboardings, module validation, full gates.

## 12. Boundaries

### Always

- validasi dan authorization server-side;
- transaction dan deterministic lock order untuk mutation;
- audit lifecycle;
- explicit business date pada query/finalization;
- update spec/ADR sebelum mengubah semantics.

### Ask first

- menambah dependency/library;
- memilih satu employment status final tertentu bila master ambigu;
- mempublikasikan integration event;
- menambah automatic account revocation atau Payroll/Attendance side effect;
- mengizinkan backdate atau reopen terminal.

### Never

- hard delete histori;
- menyimpan binary;
- mengubah Employee/Contract dari frontend;
- menganggap checklist ready sama dengan employment terminated;
- import internal model/service lintas project sebagai integration contract;
- commit secret atau fixture PII nyata.

## 13. Open questions dan deferred gates

- Bila terdapat lebih dari satu Employment Status final aktif, finalization harus meminta pilihan eksplisit dan tidak menebak.
- Account disable/revoke memerlukan kebijakan Console terpisah.
- Event `EmployeeOffboardingCompletedV1` telah dipublikasikan setelah consumer gate dan delivery/retry/ordering semantics disetujui. Event lain seperti `EmployeeOffboardingStarted` atau `EmploymentTerminated` tetap deferred sampai ada approval terpisah. Lihat [ADR-004](decisions/004-defer-integration-event-v1.md).
- Attendance dan Payroll baru boleh bereaksi melalui integration contract yang disetujui.
