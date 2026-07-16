# Tasks: HR Offboardings

Task dijalankan berurutan. Setiap task harus meninggalkan project buildable dan teruji. Specification/ADR diperbarui sebelum perubahan semantics.

## ✅ Task 01 — Module dan state contract

**Tujuan:** membuat boundary formal Offboardings tanpa route mutation, schema bisnis, atau UI aktif.

**Files yang disentuh:**

- `app/Modules/HR/Offboardings/module.php`
- `app/Modules/HR/Offboardings/permissions.php`
- `app/Modules/HR/Offboardings/navigation.php`
- `app/Modules/HR/Offboardings/routes.php`
- `app/Modules/HR/Offboardings/Providers/OffboardingsServiceProvider.php`
- `app/Modules/HR/Offboardings/Enums/{OffboardingStatus,OffboardingTaskStatus}.php`
- `tests/Feature/OffboardingFoundationTest.php`

**Acceptance criteria:**

- [x] Manifest hanya mendeklarasikan Employees, EmploymentStatuses, Console Users, dan optional EmployeeContracts.
- [x] State transition fail-closed dan terminal state immutable.
- [x] Permission contract terdaftar dengan role default least privilege.
- [x] Route dan navigation runtime kosong sampai policy-backed slice tersedia.
- [x] Event/listener/integration event public tetap kosong.

**Hasil:** selesai 2026-07-16. Foundation menambahkan module/provider, permission least-privilege, state `DRAFT -> IN_PROGRESS -> READY_FOR_EXIT -> COMPLETED` dengan cancellation fail-closed, serta task state yang dapat direopen. Belum ada schema bisnis, controller, route runtime, atau menu sehingga user tidak melihat fitur setengah jadi.

**Test:** `php artisan module:validate && php artisan test --filter=OffboardingFoundation`

**Dependencies:** specification, ADR-001, ADR-002 accepted. **Scope:** M.

## ✅ Task 02 — Template checklist vertical slice

**Tujuan:** HR dapat membuat dan melihat template beserta ordered items.

**Files:** migration/models template, request/DTO/service/transaction, controller/policy/routes, UI template, feature test.

**Acceptance criteria:**

- [x] Code unik dan items tersimpan atomic sesuai urutan.
- [x] Signed due offset terhadap exit date tervalidasi.
- [x] Authorized create/list bekerja; guest/unauthorized ditolak.

**Hasil:** selesai 2026-07-16. Template dan maksimal 100 ordered items dibuat dalam satu transaction, code/category dinormalisasi, due offset dibatasi `-365..365`, list dipaginate 20 item, dan audit hanya membawa metadata aman. Runtime hanya membuka route create/list dengan authentication serta policy; UI menyediakan empty state, reordering keyboard-accessible controls, signed offset, dan permission-aware form. Archive/restore belum tersedia sesuai scope Task 03.

**Test:** `php artisan test --filter=OffboardingTemplate && npm run typecheck && npm run build`

**Dependencies:** Task 01. **Scope:** pecah backend/UI menjadi increment M.

## ✅ Task 03 — Archive dan restore template

**Tujuan:** menjaga histori template tanpa hard delete.

**Files:** template policy/service/routes, archive UI/filter, feature test.

**Acceptance criteria:**

- [x] Archived template tidak dapat dipakai case baru.
- [x] Restore menjaga uniqueness.
- [x] Used template tetap dapat dibaca dan tidak memiliki force-delete route.

**Hasil:** selesai 2026-07-16. Archive memakai soft delete dan mengeluarkan template dari query default, sementara filter eksplisit menampilkan histori beserta ordered items. Code tetap direservasi oleh unique constraint selama archived sehingga restore tidak merebut identity template lain. Archive/restore memakai transaction, row lock, policy, dan audit; route force delete tidak tersedia. Karena Offboarding case baru dibuat pada Task 04, perlindungan histori saat ini dijamin dengan mempertahankan template/items tanpa hard delete dan akan diperkuat oleh foreign key restrict pada snapshot source.

**Test:** `php artisan test --filter=OffboardingTemplateArchive`

**Dependencies:** Task 02. **Scope:** M.

## ✅ Checkpoint A — Template contract

- [x] Module validation, targeted test, Pint, typecheck, dan build hijau.
- [x] Review memastikan template bukan live source untuk case existing.

**Evidence 2026-07-16:** isolated migration `up/down`, migration SQL preview, module validation, 13 targeted tests dengan 130 assertions, Pint, TypeScript, dan production build 2.183 modules lulus. Review correctness/security/architecture memastikan template hanya menjadi source saat draft dibuat; Task 04 wajib menyimpan snapshot lengkap dan tidak membaca current template untuk histori/progress. Lihat [laporan Checkpoint A](01-template-checkpoint-a.md).

**Gate Task 04:** snapshot wajib menyimpan source item id, content, category, required flag, order, signed due offset, calculated due date, default assignment context, dan initial status dalam transaction yang sama. Edit/archive template tidak boleh mengubah task existing. Draft tidak boleh mengubah Employee atau Contract.

## ✅ Task 04 — Draft dan task snapshot

**Tujuan:** membuat draft Offboarding dengan task snapshot atomic.

**Files:** migrations/models case/task, request/DTO, service/transaction, controller/policy/routes, basic UI, feature test.

**Acceptance criteria:**

- [x] Employee, contract, target final status, template, exit date/type/reason, dan owner tervalidasi.
- [x] Case dan seluruh task snapshot dibuat atomic.
- [x] Edit/archive template tidak mengubah task existing.

**Hasil:** selesai 2026-07-16. HR berizin dapat membuat dan melihat draft offboarding dari employee aktif, contract aktif opsional milik employee yang sama, template aktif, target employment status final, owner aktif, tanggal keluar non-backdate, jenis keluar terkontrol, alasan, dan notes opsional. Aggregate case beserta ordered task snapshot dibuat dalam satu transaction; referensi bisnis diperiksa ulang dengan row lock, audit failure merollback seluruh aggregate, dan perubahan/archive template tidak mengubah task existing. Slice ini hanya membaca Employee/Contract dan tidak menghasilkan side effect employment.

**Test:** `php artisan test --filter=OffboardingDraftSnapshot && npm run typecheck && npm run build`

**Dependencies:** Task 03. **Scope:** L outcome; wajib pecah backend/UI/test.

## ✅ Task 05 — Duplicate active dan idempotency guard

**Tujuan:** mencegah case ganda untuk employment identity sama.

**Files:** identity/fingerprint support, transaction guard, migration constraint, tests.

**Acceptance criteria:**

- [x] Retry identik mengembalikan case yang sama.
- [x] Request berbeda untuk identity aktif sama ditolak.
- [x] Concurrent create tidak menghasilkan dua case aktif.

**Hasil:** selesai 2026-07-16. Draft aktif memakai identity stabil `employee:{id}` dan fingerprint SHA-256 dari payload bisnis ternormalisasi. Retry identik mengembalikan aggregate beserta task existing tanpa audit baru. Payload berbeda untuk employee aktif yang sama ditolak, sementara unique nullable constraint melindungi competing insert di database. Identity/fingerprint tetap internal dan tidak masuk props atau audit values. Lihat [ADR-003](decisions/003-active-identity-and-idempotency.md).

**Test:** `php artisan test --filter=OffboardingDuplicateGuard`

**Dependencies:** Task 04. **Scope:** M.

## Task 06 — Detail dan progress read model

**Tujuan:** menampilkan ordered tasks dan progress deterministic.

**Files:** read service, controller props/types, detail UI, backend/frontend tests.

**Acceptance criteria:**

- [ ] Required/optional incomplete, completed, skipped, dan overdue benar.
- [ ] Business date eksplisit.
- [ ] Props tidak mengekspos PII/internal identity/fingerprint.

**Test:** `php artisan test --filter=OffboardingProgress && npm run test:frontend`

**Dependencies:** Task 04. **Scope:** M.

## Checkpoint B — Draft snapshot

- [ ] Create/list/detail bekerja end-to-end.
- [ ] Atomicity, idempotency, authorization, audit, dan snapshot invariance terbukti.

## Task 07 — Activate offboarding

**Tujuan:** menerapkan `DRAFT -> IN_PROGRESS` secara atomic.

**Files:** activation request/service/route, lifecycle UI, audit, feature test.

**Acceptance criteria:**

- [ ] Hanya draft eligible dapat diaktifkan.
- [ ] Employee/contract/duplicate guard diperiksa ulang dengan lock.
- [ ] Retry activation idempotent tanpa audit ganda.

**Test:** `php artisan test --filter=OffboardingActivation`

**Dependencies:** Task 05. **Scope:** M.

## Task 08 — Assignment dan task completion

**Tujuan:** mengelola assignee serta task start/complete.

**Files:** task requests/DTO/service/routes/policy, controls UI, tests.

**Acceptance criteria:**

- [ ] Assignee Console User valid atau unassigned.
- [ ] Completion menyimpan actor/time/note.
- [ ] Terminal/archived case menolak task mutation.

**Test:** `php artisan test --filter=OffboardingTaskCompletion`

**Dependencies:** Task 07. **Scope:** M.

## Task 09 — Controlled skip, reopen, dan ready

**Tujuan:** menutup exception task dan readiness tanpa mengakhiri employment.

**Files:** task reason requests, lifecycle service, ready route/dialog, tests.

**Acceptance criteria:**

- [ ] Required skip memerlukan permission khusus dan reason.
- [ ] Ready ditolak bila required task belum terminal.
- [ ] Reopen task pada ready mengubah case kembali `IN_PROGRESS`.

**Test:** `php artisan test --filter=OffboardingTaskLifecycle && php artisan test --filter=OffboardingReadiness`

**Dependencies:** Task 08. **Scope:** pecah task lifecycle dan ready.

## Task 10 — Cancel lifecycle

**Tujuan:** membatalkan case aktif dengan evidence.

**Files:** cancel request/service/route/dialog, audit, tests.

**Acceptance criteria:**

- [ ] Draft/in-progress/ready dapat cancel dengan reason.
- [ ] Completed/cancelled immutable.
- [ ] Cancel tidak mengubah Employee atau Contract.

**Test:** `php artisan test --filter=OffboardingCancellation`

**Dependencies:** Task 09. **Scope:** M.

## Checkpoint C — Operational lifecycle

- [ ] Transition/denial matrix dan audit hijau.
- [ ] Readiness tidak menghasilkan employment side effect.

## Task 11 — Employment termination boundary

**Tujuan:** menyediakan interface mutation resmi dari Employees dan Employee Contracts untuk finalization.

**Files:** contract/DTO/adapter/provider binding di owner modules, contract tests, ADR/spec update.

**Acceptance criteria:**

- [ ] Offboardings tidak import model/service internal owner module.
- [ ] Interface menggunakan identifier, expected state, effective date, reason, dan actor context minimal.
- [ ] Contract fail-closed untuk archived/stale/terminal resource.

**Test:** `php artisan test --filter=OffboardingTerminationContract`

**Dependencies:** Checkpoint C dan ADR-002. **Scope:** M per owner module.

## Task 12 — Atomic effective finalization

**Tujuan:** menerapkan `READY_FOR_EXIT -> COMPLETED` tepat satu kali.

**Files:** finalize request/DTO/service/transaction/route, adapters, UI, tests.

**Acceptance criteria:**

- [ ] Sebelum exit date ditolak.
- [ ] Employee, Contract, dan Offboarding berubah bersama atau rollback.
- [ ] Retry/concurrent finalize idempotent dan audit tidak ganda.

**Test:** `php artisan test --filter=OffboardingFinalization`

**Dependencies:** Task 11. **Scope:** L outcome; pecah happy path, failure, concurrency.

## Task 13 — Finalization authorization matrix

**Tujuan:** menutup abuse cases finalization.

**Files:** policy/routes, denial matrix test, security review doc.

**Acceptance criteria:**

- [ ] Guest, viewer, officer tanpa finalize, IDOR, archived, stale contract, dan invalid final status ditolak.
- [ ] Error tidak mengekspos PII/internal state.
- [ ] Seluruh privileged mutation memiliki auth dan policy middleware.

**Test:** `php artisan test --filter=OffboardingAuthorizationMatrix`

**Dependencies:** Task 12. **Scope:** M.

## Checkpoint D — Effective exit

- [ ] Atomicity, retry, concurrency, audit, dan denial terbukti.
- [ ] Employees/Contracts konsisten pada exit date.
- [ ] Tidak ada direct Attendance/Payroll mutation.

## Task 14 — Filters, archive/restore, dan due command

**Tujuan:** menyiapkan operasi harian dan histori.

**Files:** filter request/query, archive service/routes, command, UI, tests.

**Acceptance criteria:**

- [ ] Filter deterministic dan paginated.
- [ ] Hanya terminal case dapat archive/restore; tidak ada force delete.
- [ ] Due command read-only dengan input/exit code jelas.

**Test:** `php artisan test --filter=OffboardingArchive && php artisan test --filter=OffboardingsDueCommand`

**Dependencies:** Checkpoint D. **Scope:** pecah query/archive/command.

## Task 15 — Frontend completion dan quality gates

**Tujuan:** menyelesaikan UX, accessibility, responsive behavior, dan docs.

**Files:** pages/components/types/tests, docs/roadmap.

**Acceptance criteria:**

- [ ] State/empty/error/action visibility jelas.
- [ ] Dialog keyboard/focus dan mobile layout layak.
- [ ] Docs tidak mengklaim event/downstream non-MVP.

**Test:** full quality checkpoint.

**Dependencies:** Task 14. **Scope:** M per increment.

## Task 16 — Integration event v1 (deferred gate)

**Tujuan:** mempublikasikan event minimal hanya setelah consumer disetujui.

**Acceptance criteria:**

- [ ] Schema versioned dan tidak membawa PII.
- [ ] Delivery/retry/ordering semantics disetujui consumer.
- [ ] Tanpa consumer, task tetap deferred dan manifest event kosong.

**Test:** `php artisan test --filter=OffboardingIntegrationContract`

**Dependencies:** Task 15 + consumer approval. **Scope:** M.

## Final quality checkpoint

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

- [ ] Semua command hijau.
- [ ] Mutation authorization matrix lengkap.
- [ ] Security review tidak menemukan hard delete, PII/secret leak, partial finalization, atau frontend-only authorization.
- [ ] README/spec/plan/tasks/ADR dan HR roadmap sesuai implementasi.
