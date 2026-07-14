# Tasks: HR Onboardings

Task dijalankan berurutan dan specification/ADR direvisi terlebih dahulu bila implementasi membutuhkan perubahan semantics.

## ✅ Task 01 — Module dan state contract

**Tujuan:** membuat boundary module formal tanpa UI atau CRUD bisnis.

**Files yang disentuh:** `app/Modules/HR/Onboardings/{module.php,routes.php,permissions.php,navigation.php}`, provider, enums/state contract, module contract test.

**Acceptance criteria:**

- [x] Manifest dependency hanya menunjuk Employees, optional EmployeeContracts contract, dan Console Users boundary.
- [x] Permission dan transition state terdokumentasi serta tervalidasi.
- [x] Belum ada mutation route yang dapat dipanggil tanpa policy.

**Hasil:** selesai 2026-07-15. Foundation mengekspor permission contract, tetapi route dan navigation runtime tetap dinonaktifkan sampai vertical slice yang dilindungi policy tersedia.

**Test:** `php artisan module:validate && php artisan test --filter=OnboardingFoundation`

**Dependencies:** specification dan ADR-001 accepted. **Scope:** M; pecah scaffold dan state jika lebih dari lima file per increment.

## ✅ Task 02 — Template checklist vertical slice

**Tujuan:** HR dapat membuat dan melihat template dengan ordered items.

**Files yang disentuh:** migrations/models template, request/DTO/service/transaction, controller/routes/policy, template feature test, UI template components.

**Acceptance criteria:**

- [x] Code unik, item order, required flag, category, dan due offset tersimpan tepat.
- [x] Input invalid/duplicate ditolak tanpa partial items.
- [x] Authorized list/create bekerja; guest dan unauthorized ditolak.

**Hasil:** selesai 2026-07-15. Template dan ordered item dibuat dalam satu transaction, mutation diaudit, serta halaman list/create hanya tersedia melalui route yang dilindungi authentication dan policy.

**Test:** `php artisan test --filter=OnboardingTemplate && npm run typecheck && npm run build`

**Dependencies:** Task 01. **Scope:** pecah backend dan UI menjadi increment M terpisah.

## ✅ Task 03 — Archive dan restore template

**Tujuan:** menjaga histori template tanpa hard delete.

**Files yang disentuh:** template policy/service/routes, archive UI/filter, feature test.

**Acceptance criteria:**

- [x] Archive mengeluarkan template dari pilihan onboarding baru.
- [x] Restore menjaga uniqueness.
- [x] Force-delete route tidak tersedia dan template terpakai tetap dapat dibaca.

**Hasil:** selesai 2026-07-15. Archive memakai soft delete, item checklist tetap tersimpan, restore hanya menerima template terarsip, dan code tetap direservasi oleh unique constraint selama lifecycle template.

**Test:** `php artisan test --filter=OnboardingTemplateArchive`

**Dependencies:** Task 02. **Scope:** M.

## ✅ Checkpoint A — Template contract

- [x] Module validation, Pint, targeted backend test, typecheck, dan build hijau.
- [x] Review memastikan template tidak menjadi live source untuk onboarding existing.

**Evidence 2026-07-15:** `module:validate`, Pint, ESLint, Prettier, TypeScript, production build, dan 10 targeted test dengan 73 assertions lulus. Review correctness/security/architecture memastikan tidak ada hard delete atau route tanpa policy; list juga dipaginate 20 item untuk menghindari query tanpa batas.

**Gate Task 04:** onboarding task wajib menyimpan snapshot title, description, category, required flag, sort order, dan due-offset context dalam transaction yang sama. Test Task 04 harus membuktikan edit atau archive template setelah create tidak mengubah task existing; onboarding tidak boleh membaca template item sebagai live source.

## ✅ Task 04 — Draft onboarding dan task snapshot

**Tujuan:** membuat vertical slice pertama: create/list draft onboarding dengan task hasil salinan template.

**Files yang disentuh:** onboarding/task migrations/models, create request/DTO, service/transaction, controller/routes/policy, feature test, basic form/list UI.

**Acceptance criteria:**

- [x] Employee, optional contract, template, start date, dan owner tervalidasi.
- [x] Onboarding dan seluruh task snapshot dibuat atomic.
- [x] Edit template setelah create tidak mengubah task existing.

**Hasil:** selesai 2026-07-15. Draft dan tasks dibuat dalam satu transaction; fault injection pada audit membuktikan rollback menyeluruh. Snapshot menyimpan source id, content, required flag, category, sort order, due offset, calendar due date, default role, dan initial status tanpa membaca template secara live.

**Test:** `php artisan test --filter=OnboardingDraftSnapshot && npm run typecheck && npm run build`

**Dependencies:** Task 03 dan keputusan employment-period fallback. **Scope:** L secara outcome; wajib dipecah backend contract, UI, dan tests per increment.

## ✅ Task 05 — Duplicate active dan idempotency guard

**Tujuan:** mencegah case ganda untuk employment period yang sama.

**Files yang disentuh:** service/query guard, transaction/locking, request idempotency handling, feature/concurrency tests.

**Acceptance criteria:**

- [x] Retry identik tidak membuat case/task kedua.
- [x] Request berbeda untuk period aktif yang sama ditolak.
- [x] Concurrent create tidak menghasilkan dua onboarding aktif.

**Hasil:** selesai 2026-07-15. Server membentuk active identity dan SHA-256 request fingerprint secara deterministik. Guard dilakukan sebelum transaction, diulang dengan row lock, dan dipertahankan oleh unique database constraint; recovery unique-race membedakan retry identik dari conflict tanpa audit ganda.

**Test:** `php artisan test --filter=OnboardingDuplicateGuard`

**Dependencies:** Task 04. **Scope:** M.

## ✅ Task 06 — Detail dan progress read model

**Tujuan:** menyajikan detail case dan progress deterministic tanpa mutation tambahan.

**Files yang disentuh:** query/read service, controller props/types, detail/progress components, backend/frontend tests.

**Acceptance criteria:**

- [x] Ordered task, required incomplete, optional incomplete, overdue, dan completed count benar.
- [x] Empty state dan archived history jelas.
- [x] Detail tidak mengekspos PII atau internal storage reference.

**Hasil:** selesai 2026-07-15. Detail memakai `business_date` eksplisit dan read service khusus. Progress membedakan task terminal untuk persentase dari count `COMPLETED`, menghitung incomplete/overdue tanpa mutation, membaca snapshot sesuai `sort_order`, dan mendukung histori soft-deleted melalui route berizin. Props hanya membawa identitas operasional minimum dan tidak membawa active identity, request fingerprint, atau source template item id.

**Test:** `php artisan test --filter=OnboardingProgress && npm run test:frontend`

**Dependencies:** Task 04. **Scope:** M per backend/frontend increment.

## ✅ Checkpoint B — Draft snapshot

- [x] Create/list/detail draft bekerja end-to-end.
- [x] Atomic rollback, idempotency, authorization, audit, dan snapshot invariance terbukti.

**Evidence 2026-07-15:** 20 targeted onboarding tests dengan 168 assertions lulus. Create membentuk aggregate dan task snapshot, list menyediakan tautan detail dengan business date eksplisit, dan detail menghitung progress dari snapshot. Fault-injected audit membuktikan rollback penuh; identical retry tidak menambah onboarding/task/audit; request conflict ditolak; route list/create/detail dilindungi policy; edit/archive template tidak mengubah task existing. Module validation, Pint, ESLint, Prettier, TypeScript, frontend tests, production build, dan `git diff --check` juga hijau.

**Gate Task 07:** activation hanya boleh mengubah `DRAFT -> IN_PROGRESS` dalam transaction, mempertahankan active identity guard, menolak state selain draft, serta tidak membuat audit ganda pada retry.

## Task 07 — Activate onboarding

**Tujuan:** menerapkan transition `DRAFT -> IN_PROGRESS` secara atomic.

**Files yang disentuh:** activation request/service/route, lifecycle UI, feature test, audit.

**Acceptance criteria:**

- [ ] Hanya draft valid yang dapat diaktifkan.
- [ ] Duplicate active guard diperiksa ulang di transaction.
- [ ] Repeated activation tidak membuat audit/side effect ganda.

**Test:** `php artisan test --filter=OnboardingActivation`

**Dependencies:** Task 05. **Scope:** M.

## Task 08 — Assignment dan task completion

**Tujuan:** mengelola assignee serta `PENDING -> IN_PROGRESS -> COMPLETED` dengan evidence ringkas.

**Files yang disentuh:** task requests/DTO/service/routes/policy, task controls UI, feature tests.

**Acceptance criteria:**

- [ ] Assignee valid dan authorized dapat memperbarui task sesuai policy.
- [ ] Completion menyimpan actor/time/note dan menghitung ulang progress.
- [ ] Completed/cancelled onboarding menolak task mutation.

**Test:** `php artisan test --filter=OnboardingTaskCompletion && npm run typecheck`

**Dependencies:** Task 07. **Scope:** M; assignment dan completion dapat menjadi dua increment.

## Task 09 — Controlled skip dan reopen task

**Tujuan:** menangani pengecualian tanpa menghilangkan audit.

**Files yang disentuh:** skip/reopen requests, lifecycle service, UI dialogs, feature tests.

**Acceptance criteria:**

- [ ] Required task skip memerlukan permission dan reason khusus.
- [ ] Reopen memerlukan reason, menghapus terminal completion secara terkontrol, dan menurunkan progress.
- [ ] Invalid/repeated transition tidak mengubah state.

**Test:** `php artisan test --filter=OnboardingTaskLifecycle`

**Dependencies:** Task 08 dan persetujuan required-skip policy. **Scope:** M per transition.

## Task 10 — Complete dan cancel onboarding

**Tujuan:** menutup lifecycle case dengan invariant dan reason yang jelas.

**Files yang disentuh:** completion/cancel requests, service/transaction/routes, UI dialogs, feature tests.

**Acceptance criteria:**

- [ ] Completion ditolak bila required task belum terminal valid.
- [ ] Cancel draft/in-progress membutuhkan reason.
- [ ] Terminal onboarding immutable terhadap generic update dan seluruh transition diaudit.

**Test:** `php artisan test --filter=OnboardingLifecycle`

**Dependencies:** Task 09. **Scope:** M per transition.

## Checkpoint C — Lifecycle complete

- [ ] Transition matrix, denial matrix, concurrency-sensitive invariant, dan audit hijau.
- [ ] Human review menyetujui progress/completion semantics sebelum operasional query.

## Task 11 — Filters, archive/restore, dan overdue command

**Tujuan:** menyiapkan operasional harian dan histori read-only.

**Files yang disentuh:** list query/filter, archive/restore services/routes, overdue command, UI filters, feature/command tests.

**Acceptance criteria:**

- [ ] Filter employee/state/template/owner/date/overdue deterministic dan paginated.
- [ ] Archive/restore menjaga uniqueness dan tidak menyediakan force delete.
- [ ] `hr:onboardings:overdue --date` read-only, deterministic, dan memiliki exit code jelas.

**Test:** `php artisan test --filter=OnboardingArchive && php artisan test --filter=OnboardingsOverdueCommand`

**Dependencies:** Task 10. **Scope:** pecah filter, archive, dan command menjadi increment M.

## Task 12 — Frontend completion dan quality gates

**Tujuan:** menyelesaikan UX, accessibility, responsive behavior, dan dokumentasi aktual.

**Files yang disentuh:** onboarding page/components/types/tests, module docs/roadmap.

**Acceptance criteria:**

- [ ] Semua state memiliki label, empty/error state, dan aksi hanya tampil sesuai permission/state.
- [ ] Keyboard/focus dialog, mobile layout, lint, format, typecheck, frontend test, dan build hijau.
- [ ] Docs tidak mengklaim fitur non-MVP atau integration event yang belum tersedia.

**Test:** full quality checkpoint di bawah.

**Dependencies:** Task 11. **Scope:** M per UI/refinement increment.

## Task 13 — Integration contract v1 (deferred gate)

**Tujuan:** mempublikasikan snapshot/event minimal hanya setelah Attendance atau consumer nyata disetujui.

**Files yang disentuh:** Contracts/DTO/schema, manifest integrations/events, contract tests, ADR/spec update.

**Acceptance criteria:**

- [ ] Schema versioned dan hanya membawa identifier/status/date/progress minimal.
- [ ] Tidak ada direct import model internal antar project.
- [ ] Jika belum ada consumer yang disetujui, task tetap deferred dan tidak membuat speculative event.

**Test:** `php artisan test --filter=OnboardingIntegrationContract`

**Dependencies:** Task 12 dan approval consumer interface. **Scope:** M.

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
- [ ] Mutation authorization matrix mencakup seluruh route baru.
- [ ] Security review memastikan tidak ada hard delete, binary storage, PII/secret leak, atau frontend-only authorization.
- [ ] README/spec/plan/tasks/ADR dan HR roadmap mencerminkan perilaku final.
