# Tasks: HR Reports

Semua task belum dikerjakan. Implementasi harus berurutan dan berhenti di setiap checkpoint untuk review. HR Reports adalah module read-only; jika sebuah task membutuhkan mutation, revisi specification dan minta approval dulu.

## Task 01 — Module dan read-only boundary ✅

**Tujuan:** membuat module `HRReports` terdaftar dengan permission, navigation, provider, dan route GET-only.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/module.php`
- `app/Modules/HR/HRReports/routes.php`
- `app/Modules/HR/HRReports/permissions.php`
- `app/Modules/HR/HRReports/navigation.php`
- `app/Modules/HR/HRReports/Providers/HRReportsServiceProvider.php`
- `tests/Feature/HRReportAuthorizationTest.php`

**Acceptance criteria:**

- [x] Module valid di `php artisan module:validate`.
- [x] Permission report baru hanya `hr-reports.view`, `hr-reports.export`, dan `hr-reports.manage`; file permission tetap menyertakan `hr.view` sebagai permission HR existing.
- [x] Route MVP hanya GET dan user tanpa permission ditolak.
- [x] Tidak ada route create/update/delete/archive/restore/apply/approve/cancel/send.

**Hasil implementasi:** selesai 2026-07-18. Module `HRReports` sudah memiliki manifest, provider, permission, navigation, route `GET /hr/reports`, controller placeholder, dan page Inertia read-only. Route inventory test memastikan namespace `hr.reports.*` hanya mengekspos `GET`.

**Test:**

```bash
php artisan test --filter=HRReportAuthorization
php artisan module:validate
```

**Dependencies:** specification dan ADR-001 approved. **Scope:** M.

## Task 02 — Headcount query contract ✅

**Tujuan:** menyediakan service/query read-only untuk headcount by Departement, Work Location, dan Employment Status.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/DTO/*`
- `app/Modules/HR/HRReports/Queries/HeadcountReportQuery.php`
- `app/Modules/HR/HRReports/Services/HeadcountReportService.php`
- `tests/Feature/HRReportHeadcountTest.php`
- `docs/projects/hr/hr-reports/specification.md` jika semantics berubah

**Acceptance criteria:**

- [x] Query menerima `asOf` eksplisit.
- [x] Count by Departement, Work Location, dan Employment Status deterministic.
- [x] Filter employment status tersedia.
- [x] Archived/deleted employee dikecualikan; employee tanpa master masuk group `Unassigned`.
- [x] Query tidak menulis database, audit, notification, queue, atau file.

**Hasil implementasi:** selesai 2026-07-18. Headcount query contract tersedia melalui `HeadcountReportService`, `HeadcountReportQuery`, `HeadcountReportFilters`, dan `HeadcountReportResult`. Semantics MVP menghitung employee non-archived dengan `active = true`, `hired_at` kosong atau `<= asOf`, dan `ended_at` kosong atau `> asOf`. Grouping tersedia untuk Departement, Work Location, dan Employment Status; filter employment status didukung.

**Test:**

```bash
php artisan test --filter=HRReportHeadcount
```

**Dependencies:** Task 01. **Scope:** M.

## Task 03 — HR Reports page vertical slice ✅

**Tujuan:** menyediakan halaman pertama `/hr/reports` untuk membaca headcount summary.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/Http/Controllers/HRReportController.php`
- `app/Modules/HR/HRReports/Http/Requests/ListHRReportRequest.php`
- `resources/js/pages/hr/hr-reports/index.tsx`
- `resources/js/pages/hr/hr-reports/types.ts`
- `resources/js/pages/hr/hr-reports/hr-report-components/*`

**Acceptance criteria:**

- [x] Authorized user dapat membuka page HR Reports.
- [x] Page menampilkan tiga summary: Departement, Work Location, Employment Status.
- [x] Empty state jelas.
- [x] Filter tanggal/status tidak menyebabkan full page error.
- [x] Frontend tidak menampilkan action mutation.

**Hasil implementasi:** selesai 2026-07-18. Page `/hr/reports` sekarang menerima filter `as_of` dan `employment_status_id`, memuat tiga report headcount dari `HeadcountReportService`, menyediakan pilihan status kerja aktif, serta menampilkan summary card dan tabel read-only. Controller memakai `ListHRReportRequest` untuk authorization dan validasi boundary.

**Test:**

```bash
npm run typecheck
npm run build
php artisan test --filter=HRReportAuthorization
```

**Dependencies:** Task 02. **Scope:** M.

## Checkpoint A — Read-only headcount report

- [ ] Task 01–03 hijau.
- [ ] Route inventory membuktikan GET-only.
- [ ] Headcount report dapat dipakai untuk validasi awal data HR.
- [ ] Docs sesuai behavior aktual.

## Task 04 — Contract expiry report

**Tujuan:** menampilkan kontrak aktif yang berakhir dalam window eksplisit.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/Queries/ContractExpiryReportQuery.php`
- `app/Modules/HR/HRReports/Services/ContractExpiryReportService.php`
- controller/request page report
- frontend report component/types
- `tests/Feature/HRReportContractExpiryTest.php`

**Acceptance criteria:**

- [ ] Query menerima `asOf` dan `withinDays` eksplisit.
- [ ] Contract expired/expiring boundary benar.
- [ ] Result tidak memuat notes internal atau compensation.
- [ ] Empty result valid.
- [ ] Tidak ada update contract/audit/notification.

**Test:**

```bash
php artisan test --filter=HRReportContractExpiry
npm run typecheck
```

**Dependencies:** Checkpoint A dan Employee Contracts. **Scope:** M.

## Task 05 — Document expiry report

**Tujuan:** menampilkan dokumen employee yang expired/expiring tanpa membuka detail sensitif DMS.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/Queries/DocumentExpiryReportQuery.php`
- `app/Modules/HR/HRReports/Services/DocumentExpiryReportService.php`
- controller/request page report
- frontend report component/types
- `tests/Feature/HRReportDocumentExpiryTest.php`

**Acceptance criteria:**

- [ ] Query menerima `asOf` dan `withinDays` eksplisit.
- [ ] Expired/expiring/not applicable semantics mengikuti Employee Documents.
- [ ] Result tidak memuat document number plaintext, DMS reference, storage path, URL, atau token.
- [ ] Empty result valid.
- [ ] Tidak ada update document/audit/notification.

**Test:**

```bash
php artisan test --filter=HRReportDocumentExpiry
npm run typecheck
```

**Dependencies:** Task 04 dan Employee Documents. **Scope:** M.

## Task 06 — Read-only report commands

**Tujuan:** menyediakan command report untuk validasi operasional dan automation non-mutating.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/Console/Commands/HRReportsSummaryCommand.php`
- `app/Modules/HR/HRReports/Console/Commands/HRReportsContractsExpiringCommand.php`
- `app/Modules/HR/HRReports/Console/Commands/HRReportsDocumentsExpiringCommand.php`
- provider/module manifest
- `tests/Feature/HRReportCommandTest.php`

**Acceptance criteria:**

- [ ] Command menerima `--date=YYYY-MM-DD`.
- [ ] Expiry command menerima `--within=0..3650`.
- [ ] Input invalid menghasilkan exit code non-zero.
- [ ] Input valid dengan hasil kosong tetap exit `0`.
- [ ] Command tidak menulis database/audit/notification/queue/file.

**Test:**

```bash
php artisan test --filter=HRReportCommand
```

**Dependencies:** Task 04–05. **Scope:** M.

## Checkpoint B — MVP reports complete

- [ ] Headcount, status summary, contract expiry, dan document expiry tersedia.
- [ ] Sensitive-data review lulus.
- [ ] Backend/frontend focused tests hijau.
- [ ] Export tetap belum dibuat.

## Task 07 — Lifecycle HR seeder untuk report

**Tujuan:** membuat data local/dev yang realistis untuk satu lifecycle HR sehingga HR Reports punya data valid untuk dibaca.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/Database/Seeders/HRReportLifecycleSeeder.php` atau lokasi seeder HR yang disetujui
- seeder registration local/dev jika disetujui
- `tests/Feature/HRReportLifecycleSeederTest.php`
- `docs/projects/hr/hr-reports/README.md`
- `docs/projects/hr/module-guide.md` bila ada aturan seeder baru

**Acceptance criteria:**

- [ ] Seeder idempotent dan aman dijalankan ulang.
- [ ] Data seed mencakup master HR, employees, contracts, documents, movements, onboarding, dan offboarding minimal.
- [ ] Report MVP menghasilkan data non-kosong dari seeder.
- [ ] Seeder tidak membuat password/email/file/storage data production nyata.
- [ ] Seeder tidak mengubah data manual di luar namespace/kode seed.

**Test:**

```bash
php artisan db:seed --class="App\\Modules\\HR\\HRReports\\Database\\Seeders\\HRReportLifecycleSeeder"
php artisan test --filter=HRReportLifecycleSeeder
php artisan test --filter=HRReport
```

**Dependencies:** Checkpoint B. **Scope:** M/L; pecah menjadi master seed, employee lifecycle seed, dan assertion test bila perlu.

## Task 08 — Frontend polish dan accessibility

**Tujuan:** membuat page report nyaman dibaca oleh user HR pemula tanpa overload informasi.

**Files yang disentuh:**

- `resources/js/pages/hr/hr-reports/index.tsx`
- `resources/js/pages/hr/hr-reports/hr-report-components/*`
- frontend presenter tests bila pattern tersedia
- docs user guide bila perlu

**Acceptance criteria:**

- [ ] Summary card/table responsive.
- [ ] Filter label mudah dipahami.
- [ ] Empty state memberi arahan data apa yang harus diinput lebih dulu.
- [ ] Tidak ada tombol mutation.
- [ ] Build/typecheck/lint hijau.

**Test:**

```bash
npm run format:check
npm run lint:check
npm run typecheck
npm run build
```

**Dependencies:** Task 07. **Scope:** M.

## Final quality checkpoint

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

- [ ] Semua command hijau.
- [ ] Route inventory tetap read-only.
- [ ] Seeder lifecycle HR idempotent.
- [ ] Sensitive-data review lulus.
- [ ] README/spec/plan/tasks/ADR sesuai implementasi aktual.
