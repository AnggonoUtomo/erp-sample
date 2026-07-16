# Checkpoint C — Offboarding Operational Lifecycle

**Status:** PASS

**Tanggal:** 2026-07-16

**Verified baseline:** working tree berbasis `fa44ab6`

## Scope yang diverifikasi

Checkpoint ini menutup Task 07–10:

- activation `DRAFT -> IN_PROGRESS`;
- assignment, start, dan completion task;
- controlled skip dan reopen task;
- readiness `IN_PROGRESS -> READY_FOR_EXIT`;
- readiness revocation ketika task terminal dibuka kembali;
- cancellation dari seluruh state aktif;
- authorization, input validation, transaction, row lock, audit, rollback, idempotency, dan UI lifecycle.

Checkpoint ini belum mencakup employment termination contract, effective finalization, finalization authorization matrix, archive/restore case, operational due command, atau integration event.

## Evidence quality gate

| Gate | Hasil |
| --- | --- |
| `php artisan module:validate` | PASS |
| `php artisan test --filter=Offboarding` | PASS — 49 tests, 500 assertions |
| `php artisan test --filter=MutationRouteAuthorization` | PASS — 1 test, 125 assertions |
| `php artisan test` | PASS — 420 tests, 2.375 assertions |
| `vendor/bin/pint --test` | PASS |
| `npm run lint:check` | PASS |
| `npm run format:check` | PASS |
| `npm run typecheck` | PASS |
| `npm run test:frontend` | PASS — 11 files, 17 tests |
| `npm run build` | PASS — 2.193 modules transformed |
| `git diff --check` | PASS |

## Transition dan denial matrix

| Mutation | State diterima | State/condition ditolak | Evidence utama |
| --- | --- | --- | --- |
| Activate | `DRAFT` | archived, terminal, identity/reference stale, duplicate active case | status audit; retry `IN_PROGRESS` idempotent |
| Assign task | `DRAFT`, `IN_PROGRESS`; task non-terminal | archived, ready/terminal case, terminal task, deleted assignee, cross-aggregate task | assignee before/after audit |
| Start task | case `IN_PROGRESS`, task `PENDING` | state lain, archived, unauthorized | task status audit; retry idempotent |
| Complete task | case `IN_PROGRESS`, task `IN_PROGRESS` | state lain, archived, unauthorized | actor, timestamp, note, audit |
| Skip optional task | case `IN_PROGRESS`, task pending/in-progress | terminal task/case, archived, missing reason | actor, timestamp, reason, audit |
| Skip required task | sama dengan optional + permission khusus | tanpa `offboardings.task-skip-required` | actor, timestamp, reason, audit |
| Reopen task | case `IN_PROGRESS` atau `READY_FOR_EXIT`, task terminal | task non-terminal, archived/terminal case | reopen actor/time/reason; terminal evidence dibersihkan |
| Mark ready | `IN_PROGRESS`, seluruh required task terminal | required incomplete, archived, draft/terminal case | status audit; retry ready idempotent |
| Cancel | `DRAFT`, `IN_PROGRESS`, `READY_FOR_EXIT` | completed, cancelled, archived, unauthorized, missing reason | actor, timestamp, reason; active identity dilepas |

Seluruh route mutation di atas memakai authentication dan policy middleware. Scoped binding menolak task dari aggregate lain sebelum service mutation dijalankan.

## Atomicity dan audit review

- Aggregate dan task dikunci dengan `lockForUpdate()` dalam transaction module.
- Audit berada dalam transaction yang sama dengan mutation.
- Failure audit yang diinjeksi terbukti me-rollback activation, task completion, skip, readiness, dan cancellation.
- Reopen dari `READY_FOR_EXIT` mengubah task ke `PENDING` dan aggregate ke `IN_PROGRESS` dalam transaction yang sama.
- Cancellation me-rollback status, cancellation evidence, dan active identity bila audit gagal.
- Retry activation/start/readiness tidak menghasilkan audit ganda.
- Repeated invalid terminal transition fail-closed dan tidak membuat audit baru.

## Employment side-effect boundary

- Activation tidak mengubah Employee, optional Contract, exit context, atau task snapshot.
- Task assignment/completion/skip/reopen hanya mengubah Offboarding task evidence dan, khusus reopen ready, process status Offboarding.
- `READY_FOR_EXIT` hanya checkpoint kesiapan proses. Tidak ada perubahan Employee atau Contract.
- Cancellation mempertahankan Employee, optional Contract, exit context, dan task snapshot; hanya active identity proses yang dilepas.
- Tidak ada direct mutation Attendance/Payroll dan tidak ada integration event yang dipublikasikan.

Boundary ini konsisten dengan [ADR-001](decisions/001-checklist-snapshot-and-exit-readiness.md) dan [ADR-002](decisions/002-effective-dated-termination-boundary.md).

## Authorization dan security review

- Permission dipisahkan untuk activation, task update, required skip, mark-ready, dan cancel.
- Reason/note divalidasi server-side dan dibatasi maksimum 2.000 karakter.
- Frontend visibility bukan security boundary; seluruh endpoint tetap policy-backed.
- Active identity dan request fingerprint tetap tersembunyi dari Inertia props.
- Read model menggunakan field allowlist dan hanya mengekspos identifier/nama actor yang dibutuhkan.
- React merender reason/note sebagai text dan tidak memakai raw HTML.
- Tidak ada force delete atau mutation employment yang terbuka pada checkpoint ini.

## Correctness, maintainability, dan performance review

- State transition memakai enum fail-closed.
- Lifecycle orchestration terpisah antara activation, task service, dan aggregate lifecycle service.
- Controller tetap tipis dan FormRequest menangani boundary input.
- Detail eager-load assignee dan actor evidence sehingga tidak ada N+1 per task.
- UI lifecycle dipisah menjadi activation, task controls, reason, ready, dan cancel dialog.
- Tidak ada dependency frontend/backend baru.

## Findings

Tidak ada blocker untuk Task 11.

Residual constraints yang memang belum masuk scope:

- `READY_FOR_EXIT -> COMPLETED` belum tersedia;
- Offboardings masih memakai model Employee/Employee Contract pada read/create/activation lama, tetapi finalization tidak boleh menambah direct internal mutation dependency;
- terminal cancellation belum dapat direopen sesuai non-scope MVP;
- archive/restore case dan due command belum tersedia;
- browser smoke test manual untuk focus, keyboard, mobile viewport, dan destructive dialog masih direkomendasikan sebelum production.

## Gate decision

Checkpoint C **PASS**. Task 11 — Employment termination boundary boleh dimulai dengan syarat:

- kontrak mutation didefinisikan dan dimiliki module Employees serta Employee Contracts;
- Offboardings hanya bergantung pada interface publik, bukan model/service internal owner module untuk finalization;
- request contract membawa identifier, expected state, effective date, reason, dan actor context minimum;
- adapter fail-closed terhadap resource archived, stale, inactive, atau terminal;
- belum ada route finalization sampai contract owner modules dan contract tests lulus.
