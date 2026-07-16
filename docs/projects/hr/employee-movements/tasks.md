# Tasks: Employee Movements

## Task 01 — Transfer hari ini vertical slice

**Tujuan:** create/list DRAFT dan apply transfer atomik ke Employees.

**Files:** module contract, migration/model, request/DTO/service/transaction/controller/policy/provider, page typed, feature test.

**Acceptance criteria:**

- [x] DRAFT menyimpan server-generated before/after dan minimal satu perubahan.
- [x] Histori before/after tersedia sesuai permission.
- [x] Apply hari ini atomic, audited, stale-safe, dan idempotency denial teruji.

**Hasil implementasi:** selesai 2026-07-13. Test fokus lulus dengan 4 skenario; quality gates dicatat pada README setelah verifikasi final.

**Test:** `php artisan test --filter=HREmployeeMovement`

## Task 02 — Promotion/demotion + Job Level

**Tujuan:** melengkapi movement bertipe `PROMOTION` dan `DEMOTION` agar perubahan job level masuk ke histori before/after dan diterapkan atomik ke profile Employees.

**Files:** request/DTO/service movement, page typed, feature test, specification, implementation plan, ADR, README.

**Acceptance criteria:**

- [x] DRAFT promotion/demotion wajib memilih job level tujuan.
- [x] Job level tujuan harus aktif dan berbeda dari job level employee saat ini.
- [x] Apply promotion/demotion memperbarui `hr_employees.job_level_id` secara atomik, audited, stale-safe, dan idempotency-safe.
- [x] Transfer tidak boleh mengubah job level; harus memakai promotion/demotion.

**Hasil implementasi:** selesai 2026-07-17. Movement sekarang mendukung `TRANSFER`, `PROMOTION`, dan `DEMOTION` dengan snapshot work profile yang mencakup job level.

**Test:** `php artisan test --filter=HREmployeeMovement`

## Task 03 — Employment Status/Type change dan contract coordination

**Tujuan:** melengkapi movement bertipe `EMPLOYMENT_CHANGE` agar perubahan employment status/type tercatat di histori before/after dan diterapkan atomik ke profile Employees dengan guard kontrak.

**Files:** module manifest, request/DTO/service movement, page typed, feature test, specification, implementation plan, ADR, README.

**Acceptance criteria:**

- [x] DRAFT employment change wajib mengubah `employment_status_id` atau `employment_type_id`.
- [x] Employment status/type tujuan harus aktif.
- [x] Perubahan employment type wajib didukung active contract yang efektif pada tanggal movement.
- [x] Apply employment change memperbarui Employees secara atomik, audited, stale-safe, dan idempotency-safe.
- [x] Transfer/promotion/demotion tidak boleh menyelundupkan perubahan employment status/type.

**Hasil implementasi:** selesai 2026-07-17. Movement sekarang mendukung `EMPLOYMENT_CHANGE` dan snapshot work profile mencakup employment status/type.

**Test:** `php artisan test --filter=HREmployeeMovement`

## Task 04 — Future-effective scheduler dan cancellation

**Tujuan:** mendukung DRAFT dengan `effective_date` hari ini atau masa depan, menyediakan cancellation untuk DRAFT, dan command scheduler untuk menerapkan movement yang sudah due.

**Files:** migration cancellation, command, provider/module manifest, request/controller/policy/service/model, page typed, feature test, specification, implementation plan, ADR, README.

**Acceptance criteria:**

- [x] DRAFT future-effective boleh dibuat; backdate ditolak.
- [x] Manual apply ditolak bila movement belum mencapai effective date.
- [x] Command `hr:employee-movements:apply-due --date=YYYY-MM-DD` menerapkan DRAFT due secara deterministik.
- [x] Command `--dry-run` tidak mengubah database.
- [x] DRAFT dapat dibatalkan dengan alasan; cancelled movement tidak dapat di-apply.

**Hasil implementasi:** selesai 2026-07-17. Movement sekarang mendukung future-effective DRAFT, cancellation, dan scheduler command due-date.

**Test:** `php artisan test --filter=HREmployeeMovement`

## Task 05 — Archive/restore, approval, dan integration snapshot/event

**Tujuan:** menutup lifecycle Employee Movements dengan approval gate sebelum apply, archive/restore untuk cancelled movement, dan event integration v1 saat movement diterapkan.

**Files:** migration approval/archive metadata, model/service/controller/policy/routes/permissions, event/schema integration, page typed, feature test, specification, implementation plan, ADR, README.

**Acceptance criteria:**

- [x] Movement harus `APPROVED` sebelum dapat di-apply manual atau scheduler.
- [x] Approval mengulang stale/profile guard agar draft lama tidak disetujui setelah profile berubah.
- [x] Cancelled movement dapat di-archive dan restore tanpa hard delete.
- [x] Apply menerbitkan `EmployeeMovementAppliedV1` dengan payload minimal dan tanpa PII bebas/alasan internal.
- [x] Manifest module mencatat event/schema integration v1.

**Hasil implementasi:** selesai 2026-07-17. Lifecycle saat ini: `DRAFT -> APPROVED -> APPLIED`, dengan cabang `DRAFT/APPROVED -> CANCELLED -> archived/restored`.

**Test:** `php artisan test --filter=HREmployeeMovement`

## Final quality checkpoint

- [x] Pint, module validate, frontend typecheck/lint/format/build, focused backend test, dan full backend suite lulus.
- [x] Diff direview untuk memastikan lifecycle dan integration payload tidak membawa PII bebas.
