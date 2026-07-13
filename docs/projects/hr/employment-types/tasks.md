# Tasks: Employment Types

Task mengikuti dependency order. Checkbox belum dicentang karena paket ini baru mendokumentasikan baseline; implementasi existing harus diverifikasi, bukan diasumsikan selesai.

## Task 01 — Baseline contract audit

**Tujuan:** membuktikan manifest, schema, route, permission, service, frontend, dan docs konsisten.

**Files:** `app/Modules/HR/EmploymentTypes/module.php`, `routes.php`, `permissions.php`, migration/model, tests, docs.

**Acceptance criteria:**

- [ ] Seluruh field/route/permission aktual tercatat.
- [ ] Module contract valid dan tidak ada dependency tersembunyi.
- [ ] Deviasi dokumentasi menjadi finding atau task eksplisit.

**Test:** `php artisan module:validate` dan targeted feature test. **Scope:** S.

## Task 02 — Validation dan invariant

**Tujuan:** mengunci keunikan code, relasi, nilai batas, dan normalization.

**Files:** FormRequest, DTO/service, targeted feature test.

**Acceptance criteria:**

- [ ] Input invalid tidak mengubah database.
- [ ] Unique rule aman saat update dan archive.
- [ ] Invariant employment type memiliki regression test.

**Test:** `php artisan test --filter=HREmploymentType`. **Dependency:** Task 01. **Scope:** M.

## Task 03 — Authorization matrix

**Tujuan:** memastikan seluruh mutation hanya dapat dilakukan permission yang tepat.

**Files:** policy/request, authorization inventory test, docs bila contract berubah.

**Acceptance criteria:**

- [ ] Guest ditolak seluruh route protected.
- [ ] Viewer ditolak seluruh mutation.
- [ ] Permission manage dan granular konsisten.

**Test:** targeted authorization test + `MutationRouteAuthorizationTest`. **Dependency:** Task 01. **Scope:** S.

### Checkpoint A — Contract aman

- [ ] Task 01–03 hijau.
- [ ] Pint, module validator, dan backend regression hijau.

## Task 04 — Lifecycle dan referential safety

**Tujuan:** mencegah delete/restore/force-delete merusak consumer.

**Files:** service/transaction/policy, relation guard, lifecycle feature test.

**Acceptance criteria:**

- [ ] Delete yang masih direferensikan fail-closed atau mengikuti keputusan terdokumentasi.
- [ ] Restore mempertahankan uniqueness.
- [ ] Force-delete tidak tersedia tanpa alasan bisnis dan permission eksplisit.

**Test:** lifecycle + database constraint tests. **Dependency:** Checkpoint A. **Scope:** M.

## Task 05 — Consumer read boundary

**Tujuan:** mengekspor DTO/read contract v1 hanya bila terdapat consumer lintas project nyata.

**Files:** Integration contract/DTO/schema, provider binding, contract test, ADR.

**Acceptance criteria:**

- [ ] Consumer tidak mengimpor model/table internal.
- [ ] Contract additive, versioned, minimal, dan fail-closed.
- [ ] Tidak dibuat bila service internal sudah cukup.

**Test:** architecture/contract tests. **Dependency:** Task 04 dan approval ADR. **Scope:** M.

## Task 06 — Frontend dan operasional

**Tujuan:** menyelaraskan UX master dengan permission dan lifecycle aktual.

**Files:** page/types/components, frontend tests, user guide.

**Acceptance criteria:**

- [ ] List paginated memiliki loading/empty/error state.
- [ ] Mutation action mengikuti permission tanpa menggantikan policy server-side.
- [ ] Form dan feedback dapat dipakai pengguna pemula.

**Test:** ESLint, Prettier, typecheck, Vitest, build, dan manual smoke test. **Dependency:** Task 04. **Scope:** M.

### Final checkpoint

- [ ] Seluruh acceptance criteria specification hijau.
- [ ] Full backend/frontend/module/security gates hijau.
- [ ] README/spec/plan/tasks/ADR sesuai implementation aktual.
