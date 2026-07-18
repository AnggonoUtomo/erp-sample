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

- [x] Task 01–03 hijau.
- [x] Route inventory membuktikan GET-only.
- [x] Headcount report dapat dipakai untuk validasi awal data HR.
- [x] Docs sesuai behavior aktual.

**Hasil checkpoint:** selesai 2026-07-18. HR Reports MVP tahap awal sudah read-only dan siap dipakai untuk validasi awal data HR melalui `/hr/reports`. Route inventory hanya mengekspos `GET /hr/reports`, permission mutation tidak tersedia di module HR Reports, query headcount tidak menulis database/audit/notification/queue/file, dan frontend tidak menyediakan action mutation.

**Evidence:**

```bash
php artisan route:list --name=hr.reports
vendor\bin\pint --test app\Modules\HR\HRReports tests\Feature\HRReportAuthorizationTest.php tests\Feature\HRReportHeadcountTest.php
php artisan test --filter=HRReport
php artisan module:validate
npm run format:check
npm run lint:check
npm run typecheck
npm run build
git diff --check
```

Semua command di atas hijau. `php artisan test --filter=HRReport` menghasilkan `8 passed (48 assertions)`.

## Task 04 — Contract expiry report ✅

**Tujuan:** menampilkan kontrak aktif yang berakhir dalam window eksplisit.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/Queries/ContractExpiryReportQuery.php`
- `app/Modules/HR/HRReports/Services/ContractExpiryReportService.php`
- controller/request page report
- frontend report component/types
- `tests/Feature/HRReportContractExpiryTest.php`

**Acceptance criteria:**

- [x] Query menerima `asOf` dan `withinDays` eksplisit.
- [x] Contract expired/expiring boundary benar.
- [x] Result tidak memuat notes internal atau compensation.
- [x] Empty result valid.
- [x] Tidak ada update contract/audit/notification.

**Hasil implementasi:** selesai 2026-07-18. HR Reports sekarang memuat report `Contract Expiry` read-only melalui `ContractExpiryReportService`, `ContractExpiryReportQuery`, dan DTO expiry report. Page `/hr/reports` menerima filter `contract_within_days` eksplisit dengan default 30 hari, menampilkan kontrak `ACTIVE` yang sudah melewati `end_date` atau akan berakhir sampai `asOf + withinDays`, dan membedakan state `EXPIRED`/`EXPIRING`. Result sengaja tidak mengirim `contract_number`, `notes`, compensation, audit, notification, queue, atau file side effect.

**Test:**

```bash
php artisan test --filter=HRReportContractExpiry
npm run typecheck
```

**Dependencies:** Checkpoint A dan Employee Contracts. **Scope:** M.

## Task 05 — Document expiry report ✅

**Tujuan:** menampilkan dokumen employee yang expired/expiring tanpa membuka detail sensitif DMS.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/Queries/DocumentExpiryReportQuery.php`
- `app/Modules/HR/HRReports/Services/DocumentExpiryReportService.php`
- controller/request page report
- frontend report component/types
- `tests/Feature/HRReportDocumentExpiryTest.php`

**Acceptance criteria:**

- [x] Query menerima `asOf` dan `withinDays` eksplisit.
- [x] Expired/expiring/not applicable semantics mengikuti Employee Documents.
- [x] Result tidak memuat document number plaintext, DMS reference, storage path, URL, atau token.
- [x] Empty result valid.
- [x] Tidak ada update document/audit/notification.

**Hasil implementasi:** selesai 2026-07-18. HR Reports sekarang memuat report `Document Expiry` read-only melalui `DocumentExpiryReportService` dan `DocumentExpiryReportQuery`. Page `/hr/reports` menerima filter `document_within_days` eksplisit dengan default 30 hari, menampilkan metadata dokumen yang sudah `EXPIRED` atau `EXPIRING`, dan mengecualikan dokumen `NOT_APPLICABLE` tanpa `expires_at`. Result sengaja hanya mengirim employee, tipe dokumen, tanggal expiry, days remaining, dan state; tidak mengirim document number, DMS reference, storage path, URL, token, notes, audit, notification, queue, atau file side effect.

**Test:**

```bash
php artisan test --filter=HRReportDocumentExpiry
npm run typecheck
```

**Dependencies:** Task 04 dan Employee Documents. **Scope:** M.

## Task 06 — Read-only report commands ✅

**Tujuan:** menyediakan command report untuk validasi operasional dan automation non-mutating.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/Console/Commands/HRReportsSummaryCommand.php`
- `app/Modules/HR/HRReports/Console/Commands/HRReportsContractsExpiringCommand.php`
- `app/Modules/HR/HRReports/Console/Commands/HRReportsDocumentsExpiringCommand.php`
- provider/module manifest
- `tests/Feature/HRReportCommandTest.php`

**Acceptance criteria:**

- [x] Command menerima `--date=YYYY-MM-DD`.
- [x] Expiry command menerima `--within=0..3650`.
- [x] Input invalid menghasilkan exit code non-zero.
- [x] Input valid dengan hasil kosong tetap exit `0`.
- [x] Command tidak menulis database/audit/notification/queue/file.

**Hasil implementasi:** selesai 2026-07-18. HR Reports sekarang menyediakan command read-only:

- `php artisan hr:reports:summary --date=2026-07-18`
- `php artisan hr:reports:contracts-expiring --date=2026-07-18 --within=30`
- `php artisan hr:reports:documents-expiring --date=2026-07-18 --within=30`

Semua command memvalidasi input tanggal/window secara eksplisit, menghasilkan exit code non-zero untuk input invalid, dan tetap exit `0` untuk hasil kosong. Output command tidak menampilkan `contract_number`, document number, DMS reference, storage path, URL, token, notes, atau field kompensasi. Test memastikan tidak ada audit log, notification, queue, atau perubahan data sumber.

**Test:**

```bash
php artisan test --filter=HRReportCommand
```

**Dependencies:** Task 04–05. **Scope:** M.

## Checkpoint B — MVP reports complete

- [x] Headcount, status summary, contract expiry, dan document expiry tersedia.
- [x] Sensitive-data review lulus.
- [x] Backend/frontend focused tests hijau.
- [x] Export tetap belum dibuat.

**Hasil checkpoint:** selesai 2026-07-18. MVP HR Reports sudah lengkap untuk report read-only awal:

- Headcount by Departement.
- Headcount by Work Location.
- Employment Status Summary.
- Contract Expiry.
- Document Expiry.

Route inventory tetap hanya mengekspos `GET /hr/reports`; tidak ada route mutation/export di namespace HR Reports. Review sensitive-data memastikan implementation tidak mengirim `contract_number`, document number plaintext, DMS reference, storage path, URL, token, notes internal, atau compensation pada report expiry. Command report juga hanya read-only dan diuji tidak membuat audit log, notification, queue, file, atau perubahan data sumber.

**Evidence:**

```bash
php artisan route:list --name=hr.reports
vendor\bin\pint --test app\Modules\HR\HRReports tests\Feature\HRReportAuthorizationTest.php tests\Feature\HRReportHeadcountTest.php tests\Feature\HRReportContractExpiryTest.php tests\Feature\HRReportDocumentExpiryTest.php tests\Feature\HRReportCommandTest.php
php artisan test --filter=HRReport
php artisan module:validate
npm run format:check
npm run lint:check
npm run typecheck
npm run build
git diff --check
```

Semua command di atas hijau. `php artisan test --filter=HRReport` menghasilkan `18 passed (179 assertions)`.

## Task 07 — Lifecycle HR seeder untuk report ✅

**Tujuan:** membuat data local/dev yang realistis untuk satu lifecycle HR sehingga HR Reports punya data valid untuk dibaca.

**Files yang disentuh:**

- `app/Modules/HR/HRReports/Database/Seeders/HRReportLifecycleSeeder.php` atau lokasi seeder HR yang disetujui
- seeder registration local/dev jika disetujui
- `tests/Feature/HRReportLifecycleSeederTest.php`
- `docs/projects/hr/hr-reports/README.md`
- `docs/projects/hr/module-guide.md` bila ada aturan seeder baru

**Acceptance criteria:**

- [x] Seeder idempotent dan aman dijalankan ulang.
- [x] Data seed mencakup master HR, employees, contracts, documents, movements, onboarding, dan offboarding minimal.
- [x] Report MVP menghasilkan data non-kosong dari seeder.
- [x] Seeder tidak membuat password/email/file/storage data production nyata.
- [x] Seeder tidak mengubah data manual di luar namespace/kode seed.

**Hasil implementasi:** selesai 2026-07-18. `HRReportLifecycleSeeder` menyediakan data local/dev dengan namespace `RPT-*`, employee number `EMP-RPT-*`, dan email dummy `example.test`. Seeder membuat master HR minimal, tiga employee lifecycle, kontrak aktif, metadata dokumen expiring, movement transfer applied, onboarding in-progress, dan offboarding draft. Seeder tidak membuat file/storage/DMS reference/token; user seed memakai password acak yang tidak diketahui. Untuk movement yang belum punya natural unique key, seeder hanya merapikan ulang movement di seed namespace milik employee `EMP-RPT-002` agar state akhir tetap idempotent tanpa menyentuh data manual.

**Test:**

```bash
php artisan db:seed --class="App\\Modules\\HR\\HRReports\\Database\\Seeders\\HRReportLifecycleSeeder"
php artisan test --filter=HRReportLifecycleSeeder
php artisan test --filter=HRReport
```

**Dependencies:** Checkpoint B. **Scope:** M/L; pecah menjadi master seed, employee lifecycle seed, dan assertion test bila perlu.

## Task 08 — Frontend polish dan accessibility ✅

**Tujuan:** membuat page report nyaman dibaca oleh user HR pemula tanpa overload informasi.

**Files yang disentuh:**

- `resources/js/pages/hr/hr-reports/index.tsx`
- `resources/js/pages/hr/hr-reports/hr-report-components/*`
- frontend presenter tests bila pattern tersedia
- docs user guide bila perlu

**Acceptance criteria:**

- [x] Summary card/table responsive.
- [x] Filter label mudah dipahami.
- [x] Empty state memberi arahan data apa yang harus diinput lebih dulu.
- [x] Tidak ada tombol mutation.
- [x] Build/typecheck/lint hijau.

**Hasil implementasi:** selesai 2026-07-18. Page HR Reports dipecah dari single-file besar menjadi component folder `hr-report-components`. Polish frontend mencakup hero read-only, filter card dengan label eksplisit, summary cards responsive, headcount cards dengan caption table, expiry cards dengan state text + badge, dan empty state yang mengarahkan user ke input master/employee/contract/document yang relevan. Tidak ada tombol atau route mutation yang ditambahkan.

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
