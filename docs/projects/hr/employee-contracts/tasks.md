# Tasks: Employee Contracts

Semua task berstatus belum dikerjakan. File adalah perkiraan; jika implementasi menemukan perbedaan, revisi spec dan task sebelum memperluas scope.

## Task 01 — Vertical slice pertama: create dan list kontrak ✅

**Tujuan:** menghasilkan jalur end-to-end terkecil agar HR dapat membuat dan melihat draft contract, tanpa activation, attachment, reminder, approval, atau Payroll integration.

**Files yang disentuh (pecah menjadi commit kecil jika melebihi lima):**

- `app/Modules/HR/EmployeeContracts/module.php`, `routes.php`, `permissions.php`, `navigation.php`
- `app/Modules/HR/EmployeeContracts/Database/Migrations/*create_hr_employee_contracts_table.php`
- backend request/DTO/model/service/transaction/controller/policy/provider pada module yang sama
- `resources/js/pages/hr/employee-contracts/index.tsx`, `types.ts`, dan komponen create/list
- `tests/Feature/HREmployeeContractTest.php`

**Acceptance criteria:**

- [x] Authorized HR dapat membuka list paginated dan membuat `DRAFT` contract.
- [x] Duplicate number, invalid date range, required end date, dan overlap ditolak tanpa partial write.
- [x] Guest dan user tanpa permission mendapat denial yang benar; create menulis audit log.

**Test:**

```bash
php artisan test tests/Feature/HREmployeeContractTest.php
php artisan module:validate
npm run typecheck && npm run build
```

**Dependencies:** Employees, EmploymentTypes, ADR-001 disetujui. **Scope:** L, wajib dieksekusi sebagai beberapa increment tetapi satu vertical outcome.

## Task 02 — Interval query dan overlap guard ✅

**Tujuan:** memusatkan aturan effective-at-date dan overlap agar semua use case memakai semantics yang sama.

**Files:** model/query object, service interval, unit/feature test, specification bila semantics berubah.

**Acceptance criteria:**

- [x] Boundary start/end inklusif, open-ended, cancelled, dan archived teruji.
- [x] Overlap lama-menutup-baru, baru-menutup-lama, boundary collision, dan open-ended ditolak.
- [x] Query memiliki perilaku deterministik pada tanggal input eksplisit.

**Test:** `php artisan test --filter=EmployeeContractInterval`

**Dependencies:** Task 01. **Scope:** M (3–5 files).

## Task 03 — Aktivasi contract ✅

**Tujuan:** mengaktifkan draft melalui transition eksplisit yang atomic dan authorized.

**Files:** activation request, service/transaction, controller/route, feature test, audit/event definition.

**Acceptance criteria:**

- [x] Hanya `DRAFT -> ACTIVE` yang diterima dan retry tidak membuat side effect ganda.
- [x] Overlap diperiksa kembali di dalam transaction dengan locking.
- [x] Generic update tidak dapat mengubah field periode contract aktif.

**Test:** `php artisan test --filter=EmployeeContractActivation`

**Dependencies:** Task 02. **Scope:** M; pecah audit/event jika melampaui lima file.

## Task 04 — Terminate dan cancel ✅

**Tujuan:** menyediakan akhir lifecycle dengan reason dan histori yang dapat diaudit.

**Files:** transition requests, lifecycle service, controller routes, feature tests, frontend actions.

**Acceptance criteria:**

- [x] Active dapat diakhiri pada effective date valid; draft/active dapat dibatalkan sesuai matrix.
- [x] Reason wajib dan tersimpan di audit tanpa mengekspos data berlebih.
- [x] Invalid/repeated transition tidak mengubah state.

**Test:** `php artisan test --filter=EmployeeContractLifecycle && npm run typecheck`

**Dependencies:** Task 03. **Scope:** M per transition; kerjakan terminate lalu cancel secara incremental.

## Task 05 — Supersede contract secara atomic

**Tujuan:** mengakhiri kontrak lama dan membuat/menautkan kontrak pengganti tanpa intermediate invalid state.

**Files:** supersede DTO/request, service/transaction, controller/route, tests, frontend workflow.

**Acceptance criteria:**

- [ ] Old contract dan replacement terhubung serta tidak overlap.
- [ ] Kegagalan replacement me-rollback seluruh perubahan.
- [ ] Self-supersede dan chain circular ditolak.

**Test:** `php artisan test --filter=EmployeeContractSupersede`

**Dependencies:** Task 04. **Scope:** M per backend/UI increment.

## Task 06 — Archive dan restore aman

**Tujuan:** mendukung lifecycle data tanpa hard delete.

**Files:** policy, service, controller/routes, tests, archive UI/filter.

**Acceptance criteria:**

- [ ] Delete memakai soft delete dan force-delete route tidak tersedia.
- [ ] Restore ditolak jika menciptakan overlap atau melanggar unique contract number.
- [ ] Archive filter dan audit tersedia sesuai permission.

**Test:** `php artisan test --filter=EmployeeContractArchive`

**Dependencies:** Task 02. **Scope:** M.

## Task 07 — Expiry query dan read-only command

**Tujuan:** menyediakan sumber daftar contract expiring tanpa notification side effect.

**Files:** query/service, console command, command registration/manifest jika diperlukan, tests, list filter.

**Acceptance criteria:**

- [ ] `--date` dan `--within` membuat hasil deterministik.
- [ ] Command read-only, exit code terdokumentasi, dan output merangkum contract yang cocok.
- [ ] Contract cancelled/archived tidak masuk hasil default.

**Test:** `php artisan test --filter=ContractsExpiringCommand`

**Dependencies:** Task 02. **Scope:** M.

## Task 08 — Snapshot contract versi 1

**Tujuan:** mendefinisikan contract lintas project tanpa memberi Payroll akses ke model internal HR.

**Files:** integration DTO/schema, projector/adapter, manifest event/integration metadata, contract tests, docs ADR/spec.

**Acceptance criteria:**

- [ ] Payload memakai `schemaVersion: 1` dan hanya field minimal dari specification.
- [ ] Snapshot effective-at-date reproducible dan tidak berubah karena update profile saat ini.
- [ ] Belum ada listener Payroll atau direct model import.

**Test:** `php artisan test --filter=EmployeeContractSnapshot`

**Dependencies:** Task 03; persetujuan interface lintas project. **Scope:** M.

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
- [ ] Diff review memastikan tidak ada secret, hard delete, direct Payroll dependency, atau perubahan di luar scope.
- [ ] README/spec/plan/tasks/ADR mencerminkan perilaku final.
