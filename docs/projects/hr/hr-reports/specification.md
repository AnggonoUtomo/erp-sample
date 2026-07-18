# Specification: HR Reports

## 1. Objective

Membangun module `HRReports` agar HR manager/officer dapat membaca ringkasan kondisi karyawan dan risiko operasional HR secara cepat, aman, dan konsisten.

Keberhasilan MVP berarti HR dapat membuka laporan headcount, status kerja, kontrak yang akan berakhir, dan dokumen yang akan berakhir tanpa menyentuh lifecycle module sumber. Report harus reproducible ketika diberikan tanggal acuan yang sama.

## 2. Assumptions

1. `Employees` adalah sumber utama profile employee aktif/arsip.
2. `Departements`, `WorkLocations`, dan `EmploymentStatuses` adalah master grouping untuk report.
3. `EmployeeContracts` tetap menjadi sumber contract expiry; HR Reports tidak menghitung ulang lifecycle kontrak di luar query resmi.
4. `EmployeeDocuments` tetap menjadi sumber document expiry; nomor dokumen dan reference DMS tidak tampil di report MVP.
5. `EmployeeMovements`, `Onboardings`, dan `Offboardings` menjadi sumber histori/funnel untuk report lanjutan, tetapi MVP hanya membaca data yang sudah aman dan stabil.
6. Semua report menerima `asOf` atau `date` eksplisit. Jika UI memakai tanggal hari ini, tanggal tersebut tetap dikirim eksplisit dari server/client.
7. Export Excel/PDF ditunda sampai query contract dan pagination stabil.

## 3. Requirements

### 3.1 Functional MVP

HR Reports menyediakan lima report read-only:

1. **Headcount by Departement**
   - Menghitung jumlah employee per departement.
   - Mendukung filter status kerja dan tanggal acuan.
   - Menampilkan departement kosong atau tidak tergantung opsi `includeEmpty`.
   - MVP menghitung employee non-archived dengan `active = true`, `hired_at` kosong atau `<= asOf`, dan `ended_at` kosong atau `> asOf`.

2. **Headcount by Work Location**
   - Menghitung jumlah employee per lokasi kerja.
   - Mendukung filter status kerja dan tanggal acuan.
   - Membantu HR memahami distribusi employee onsite/remote/branch.
   - Employee tanpa work location masuk group `Unassigned`.

3. **Employment Status Summary**
   - Menghitung jumlah employee per employment status.
   - Membantu HR membedakan active, probation, resigned, terminated, suspended, atau status lain sesuai master data.
   - Employee tanpa employment status masuk group `Unassigned`.

4. **Contract Expiry**
   - Menampilkan kontrak aktif yang berakhir dalam window tertentu.
   - Default window implementasi boleh 30 hari, tetapi service wajib menerima `withinDays` eksplisit.
   - Tidak menampilkan notes internal atau field kompensasi.

5. **Document Expiry**
   - Menampilkan metadata dokumen employee yang expired/expiring.
   - Menggunakan status expiry dari Employee Documents.
   - Tidak menampilkan nomor dokumen plaintext, storage path, URL, token, atau DMS internal reference.

### 3.2 Functional deferred

Fitur berikut ditunda dari MVP:

- New hire report berbasis onboarding.
- Resignation/termination report berbasis offboarding.
- Turnover rate dan trend periodik.
- Movement analytics promotion/transfer/demotion.
- Export Excel/PDF.
- Scheduled email report.
- Dashboard chart kompleks.
- Custom report builder.
- Snapshot/reporting warehouse terpisah.

### 3.3 Authorization

Permission awal:

```txt
hr-reports.view
hr-reports.export
hr-reports.manage
```

MVP hanya memakai `hr-reports.view`.

- `hr-manager`: dapat melihat semua report MVP.
- `hr-officer`: dapat melihat report operasional bila permission diberikan.
- `hr-viewer`: read-only report jika permission eksplisit tersedia.
- User tanpa permission tidak boleh membuka halaman report maupun menjalankan command report.

`hr-reports.export` disiapkan sebagai contract permission masa depan, tetapi route export tidak dibuat pada MVP.

### 3.4 Read-only contract

Module `HRReports` tidak boleh menyediakan route/action berikut:

```txt
POST create
PUT/PATCH update
DELETE archive/delete
POST approve/apply/cancel/restore
POST send-notification
POST export pada MVP
```

Surface yang diperbolehkan:

```txt
GET /hr/reports
GET /hr/reports/headcount/departements
GET /hr/reports/headcount/work-locations
GET /hr/reports/employment-statuses
GET /hr/reports/contracts/expiring
GET /hr/reports/documents/expiring
```

Jika implementasi memilih satu page Inertia dengan query loader terpusat, endpoint internal tetap harus mempertahankan semantics read-only.

### 3.5 Data contract

Contoh output headcount group:

```json
{
  "asOf": "2026-07-18",
  "groupBy": "departement",
  "rows": [
    {
      "id": 1,
      "code": "HR",
      "name": "Human Resources",
      "employeeCount": 12
    }
  ],
  "total": 12
}
```

Contoh output expiry row:

```json
{
  "asOf": "2026-07-18",
  "withinDays": 30,
  "rows": [
    {
      "employeeId": 1001,
      "employeeNumber": "EMP-001",
      "employeeName": "Nama Employee",
      "typeLabel": "Kontrak Kerja",
      "expiresAt": "2026-08-10",
      "daysRemaining": 23,
      "state": "EXPIRING"
    }
  ]
}
```

Field sensitif yang tidak boleh dikirim:

- document number plaintext;
- DMS opaque reference;
- storage path atau URL file;
- notes internal;
- password, token, reset link;
- salary/compensation;
- reason confidential offboarding/movement.

### 3.6 Command design

Command read-only MVP:

```bash
php artisan hr:reports:summary --date=2026-07-18
php artisan hr:reports:contracts-expiring --date=2026-07-18 --within=30
php artisan hr:reports:documents-expiring --date=2026-07-18 --within=30
```

Design command:

- `--date` wajib valid `YYYY-MM-DD`.
- `--within` integer `0..3650`.
- Exit code `0` untuk input valid termasuk hasil kosong.
- Exit code non-zero untuk input invalid.
- Output tidak mengubah database, audit, notification, queue, atau file.

### 3.7 Seeder lifecycle HR

Seeder lifecycle HR dibuat setelah report vertical slice pertama tersedia.

Tujuan seeder:

- menyediakan master HR minimum;
- membuat employee aktif, probation, contract expiring, document expiring, onboarding, offboarding, dan movement history;
- menghasilkan data realistis untuk report manual dan automated test;
- idempotent dan aman diulang di local/dev.

Seeder tidak boleh:

- membuat password/email production nyata;
- menyimpan file DMS sungguhan pada MVP seeder report;
- membuat data kompensasi sensitif;
- mengubah data user manual tanpa namespace/kode seed yang jelas.

## 4. Non-scope

- Mutation lifecycle Employees, Contracts, Documents, Movements, Onboardings, atau Offboardings.
- Export Excel/PDF pada MVP.
- Queue untuk report besar.
- Data warehouse/reporting snapshot baru.
- Chart analytics kompleks.
- Payroll, Attendance, Accounting, CRM, atau Document Management report lintas project.
- Access ke file DMS atau download document dari report.
- Prediction, KPI advanced, dan trend turnover.

## 5. Project structure

```txt
app/Modules/HR/HRReports/
  DTO/
  Http/Controllers/
  Http/Requests/
  Policies/
  Providers/
  Queries/
  Services/
  Support/
  Tests/ atau tests/Feature/HRReportTest.php
  module.php
  navigation.php
  permissions.php
  routes.php

resources/js/pages/hr/hr-reports/
  index.tsx
  types.ts
  hr-report-components/

docs/projects/hr/hr-reports/
  README.md
  specification.md
  implementation-plan.md
  tasks.md
  decisions/
```

Seeder kandidat:

```txt
app/Modules/HR/HRReports/Database/Seeders/HRReportLifecycleSeeder.php
```

Jika pattern project lebih cocok, seeder boleh ditempatkan di module sumber HR foundation, tetapi harus tetap didokumentasikan di HR Reports karena tujuannya untuk report lifecycle.

## 6. Code style

Report query harus eksplisit dan mudah diuji. Contoh style:

```php
final class HeadcountReportQuery
{
    public function byDepartement(CarbonImmutable $asOf, bool $includeEmpty = false): HeadcountReportResult
    {
        // Query read-only; no writes, no events, no notifications.
    }
}
```

Frontend type harus memisahkan filter input dan output row:

```ts
export type HeadcountGroup = 'departement' | 'work_location' | 'employment_status';

export interface HeadcountReportRow {
    id: number | null;
    code: string | null;
    name: string;
    employeeCount: number;
}
```

## 7. Testing strategy

Backend:

```bash
php artisan test --filter=HRReport
php artisan module:validate
vendor/bin/pint --test app/Modules/HR/HRReports tests/Feature/HRReportTest.php
```

Frontend:

```bash
npm run typecheck
npm run lint:check
npm run format:check
npm run build
```

Full gate:

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

Test wajib menutup:

- authorization denial;
- query kosong;
- group dengan deleted/archived/inactive master;
- tanggal eksplisit;
- expiry boundary hari ini, window, expired, expiring;
- tidak ada mutation/audit/notification side effect;
- payload tidak memuat field sensitif.

## 8. Boundaries

Always:

- Validasi semua filter di request boundary.
- Pakai tanggal eksplisit.
- Paginate/list limit untuk report detail.
- Masking/minimal payload untuk report expiry.
- Dokumentasikan perubahan semantics report.

Ask first:

- Menambah export Excel/PDF.
- Membuat snapshot/reporting table baru.
- Menambah queue job.
- Menampilkan field sensitif.
- Menggabungkan report HR dengan Payroll/Attendance.

Never:

- Mutation data dari module sumber.
- Membuat route delete/archive/apply/cancel/approve di HR Reports.
- Mengirim notification dari report.
- Membaca atau menampilkan storage path, signed URL, token, password, atau document number plaintext.
- Mengubah seeder production data tanpa namespace/idempotency jelas.

## 9. Acceptance criteria

- [ ] Dokumen specification, plan, tasks, dan ADR disetujui.
- [ ] Module `HRReports` terdaftar tetapi read-only.
- [ ] Lima report MVP tersedia dan memakai tanggal eksplisit.
- [ ] Authorization server-side menolak user tanpa permission.
- [ ] Query dan command tidak menulis database.
- [ ] Contract/document expiry tidak mengekspos field sensitif.
- [ ] Seeder lifecycle HR tersedia, idempotent, dan menghasilkan data realistis untuk report MVP.
- [ ] Quality gates hijau.

## 10. Open questions

1. Apakah employee archived/resigned tetap dihitung dalam headcount default, atau hanya status yang dianggap aktif?
2. Apakah report MVP perlu menampilkan employee name pada expiry, atau cukup employee number untuk mengurangi exposure?
3. Apakah `includeEmpty` group master kosong perlu tersedia di UI MVP atau cukup backend option?
4. Apakah seeder lifecycle HR dijalankan otomatis oleh `DatabaseSeeder` local, atau command manual saja?
