# Panduan Module Project HR

Dokumen ini adalah panduan kerja khusus untuk project `HR`. Roadmap besarnya ada di `docs/projects/hr/roadmap.md`, sedangkan dokumen ini fokus pada aturan teknis saat membuat dan mengembangkan module HR.

## Status Saat Ini

Project `HR` sudah memiliki module awal:

```txt
app/Modules/HR/
  Departements/
  Positions/
  JobLevels/
  WorkLocations/
  EmploymentStatuses/
  EmploymentTypes/
  HRReferenceData/
  OrganizationStructures/

resources/js/pages/hr/
  departements/
  positions/
  job-levels/
  work-locations/
  employment-statuses/
  employment-types/
  hr-reference-data/
  organization-structures/
```

Module `Departements` menjadi fondasi pertama untuk struktur organisasi. Module `Positions` menjadi master jabatan yang terhubung ke Departement dan akan dipakai oleh Employees, Attendance, dan Payroll. Module `JobLevels` menjadi master level/grade jabatan lintas departement untuk employee profile, approval, benefit, dan payroll. Module `WorkLocations` menjadi master lokasi kerja untuk employee profile, attendance area, payroll, dan report organisasi. Module `EmploymentStatuses` menjadi master status kerja untuk lifecycle employee, attendance eligibility, dan payroll inclusion. Module `EmploymentTypes` menjadi master tipe hubungan kerja untuk aturan kontrak, benefit, overtime, dan payroll. Module `HRReferenceData` menjadi master referensi umum HR seperti gender, marital status, education level, religion, bank, dan blood type. Module `OrganizationStructures` menjadi hierarchy formal untuk reporting line, supervisor relationship, dan fondasi approval. Module `Employees` menjadi master employee inti dengan avatar, link user, work profile, dan relasi ke master HR foundation.

## Departements vs OrganizationStructures

`Departements` menyimpan master unit kerja. Contohnya `Human Resources`, `Finance`, `Operations`, dan `IT`. Module ini dipakai untuk mengelompokkan employee, position, budget, report, dan headcount berdasarkan unit kerja.

`OrganizationStructures` menyimpan peta hierarchy organisasi. Module ini dipakai untuk menyusun parent-child organisasi, reporting line, node di organization chart, dan jalur approval yang nanti dipakai Attendance, Payroll, atau modul workflow lain.

Contoh:

```txt
Company
└── Human Resources
    ├── Recruitment Team
    ├── Training & Development
    └── Payroll Administration
```

Pada contoh tersebut, `Human Resources` adalah master `Departements`, sedangkan susunan `Company -> Human Resources -> Recruitment Team` adalah data `OrganizationStructures`.

Pisahkan keduanya agar struktur approval dan reporting line tetap fleksibel. Satu departement bisa punya beberapa team/sub-unit, satu node structure bisa mewakili position tertentu, dan reporting line bisa berkembang tanpa merusak master departement.

Struktur frontend `departements` sudah dipisah seperti pola module Console:

```txt
resources/js/pages/hr/departements/
  index.tsx
  types.ts
  departement-components/
    departement-workspace-card.tsx
    departement-table.tsx
    departement-summary-cards.tsx
    departement-shortcut-panel.tsx
    departement-form.tsx
    departement-detail-card.tsx
    departement-columns.tsx
    delete-departement-dialog.tsx
```

Struktur frontend `positions` mengikuti pola yang sama:

```txt
resources/js/pages/hr/positions/
  index.tsx
  types.ts
  position-components/
    position-workspace-card.tsx
    position-table.tsx
    position-summary-cards.tsx
    position-shortcut-panel.tsx
    position-form.tsx
    position-detail-card.tsx
    position-columns.tsx
    delete-position-dialog.tsx
```

Struktur frontend `job-levels` mengikuti pola yang sama:

```txt
resources/js/pages/hr/job-levels/
  index.tsx
  types.ts
  job-level-components/
    job-level-workspace-card.tsx
    job-level-table.tsx
    job-level-summary-cards.tsx
    job-level-shortcut-panel.tsx
    job-level-form.tsx
    job-level-detail-card.tsx
    job-level-columns.tsx
    delete-job-level-dialog.tsx
```

Struktur frontend `work-locations` mengikuti pola yang sama:

```txt
resources/js/pages/hr/work-locations/
  index.tsx
  types.ts
  work-location-components/
    work-location-workspace-card.tsx
    work-location-table.tsx
    work-location-summary-cards.tsx
    work-location-shortcut-panel.tsx
    work-location-form.tsx
    work-location-map-panel.tsx
    work-location-detail-card.tsx
    work-location-columns.tsx
    delete-work-location-dialog.tsx
```

Struktur frontend `employment-statuses` mengikuti pola yang sama:

```txt
resources/js/pages/hr/employment-statuses/
  index.tsx
  types.ts
  employment-status-components/
    employment-status-workspace-card.tsx
    employment-status-table.tsx
    employment-status-summary-cards.tsx
    employment-status-shortcut-panel.tsx
    employment-status-form.tsx
    employment-status-detail-card.tsx
    employment-status-columns.tsx
    delete-employment-status-dialog.tsx
```

Struktur frontend `employment-types` mengikuti pola yang sama:

```txt
resources/js/pages/hr/employment-types/
  index.tsx
  types.ts
  employment-type-components/
    employment-type-workspace-card.tsx
    employment-type-table.tsx
    employment-type-summary-cards.tsx
    employment-type-shortcut-panel.tsx
    employment-type-form.tsx
    employment-type-detail-card.tsx
    employment-type-columns.tsx
    delete-employment-type-dialog.tsx
```

Struktur frontend `hr-reference-data` mengikuti pola yang sama:

```txt
resources/js/pages/hr/hr-reference-data/
  index.tsx
  types.ts
  hr-reference-data-components/
    hr-reference-data-workspace-card.tsx
    hr-reference-data-table.tsx
    hr-reference-data-summary-cards.tsx
    hr-reference-data-shortcut-panel.tsx
    hr-reference-data-form.tsx
    hr-reference-data-detail-card.tsx
    hr-reference-data-columns.tsx
    delete-hr-reference-data-dialog.tsx
```

Struktur frontend `organization-structures` mengikuti pola yang sama:

```txt
resources/js/pages/hr/organization-structures/
  index.tsx
  types.ts
  organization-structure-components/
    organization-structure-workspace-card.tsx
    organization-structure-table.tsx
    organization-structure-summary-cards.tsx
    organization-structure-shortcut-panel.tsx
    organization-structure-form.tsx
    organization-structure-detail-card.tsx
    organization-structure-columns.tsx
    delete-organization-structure-dialog.tsx
```

## Akun Demo HR

Seeder menyediakan akun demo khusus HR untuk pengujian manual.

File seeder user khusus HR ada di `database/seeders/HRUserSeeder.php`.

Semua akun memakai password:

```txt
password
```

Daftar akun:

```txt
hr.manager@mail.com
  Role: hr-manager
  Akses: manage master HR yang sudah tersedia seperti Departements, Positions, JobLevels, WorkLocations, EmploymentStatuses, EmploymentTypes, HRReferenceData, dan OrganizationStructures

hr.officer@mail.com
  Role: hr-officer
  Akses: view, create, update master HR operasional seperti Departements, Positions, JobLevels, WorkLocations, EmploymentStatuses, EmploymentTypes, HRReferenceData, dan OrganizationStructures

hr.viewer@mail.com
  Role: hr-viewer
  Akses: view-only master HR
```

Role HR demo mengambil permission dari `ModulePermissionRegistry`, sehingga setiap module HR wajib mendefinisikan default role permission untuk `hr-manager`, `hr-officer`, dan `hr-viewer`.

Di Console Access Control, permission tetap dikelompokkan berdasarkan prefix permission agar mudah dipilih per module. Contoh: `departements.update` berada di grup `Departements`, `positions.update` berada di grup `Positions`, dan `work-locations.update` berada di grup `Work Locations`.

## Dummy Data Departement

Dummy data master departement tersedia di module HR:

```txt
app/Modules/HR/Departements/Database/Seeders/HRDepartementSeeder.php
```

Seeder ini menyiapkan root departement seperti `Human Resources`, `Operations`, `Finance`, dan `Information Technology`, termasuk beberapa child unit seperti `Recruitment`, `Training & Development`, `Field Operations`, `Payroll Administration`, dan `IT Support`.

Jalankan manual jika butuh sample data:

```bash
php artisan db:seed --class="App\\Modules\\HR\\Departements\\Database\\Seeders\\HRDepartementSeeder"
```

Seeder departement juga memanggil dummy data Positions:

```txt
app/Modules/HR/Positions/Database/Seeders/HRPositionSeeder.php
```

Dummy position yang tersedia mencakup jabatan seperti `HR Manager`, `Recruitment Specialist`, `Operations Supervisor`, `Payroll Officer`, dan `IT Support Specialist`.

Dummy data master job level tersedia di module HR:

```txt
app/Modules/HR/JobLevels/Database/Seeders/HRJobLevelSeeder.php
```

Seeder ini menyiapkan level seperti `Entry Level`, `Officer`, `Senior Officer`, `Supervisor`, `Manager`, `Head of Department`, dan `Director`.

Dummy data master work location tersedia di module HR:

```txt
app/Modules/HR/WorkLocations/Database/Seeders/HRWorkLocationSeeder.php
```

Seeder ini menyiapkan lokasi seperti `Head Office Jakarta`, `Bandung Branch`, `Surabaya Branch`, dan `Remote Indonesia`, termasuk koordinat awal dan radius geofence untuk lokasi fisik.

Dummy data master employment status tersedia di module HR:

```txt
app/Modules/HR/EmploymentStatuses/Database/Seeders/HREmploymentStatusSeeder.php
```

Seeder ini menyiapkan status seperti `Probation`, `Permanent`, `Contract`, `Intern`, `Suspended`, `Resigned`, dan `Terminated`. Field operasional yang tersedia:

- `requires_attendance`: apakah employee dengan status ini wajib attendance.
- `included_in_payroll`: apakah status ini masuk payroll rutin.
- `is_final_status`: field teknis untuk menandai **status akhir** lifecycle employee seperti resigned/terminated.

Dummy data master employment type tersedia di module HR:

```txt
app/Modules/HR/EmploymentTypes/Database/Seeders/HREmploymentTypeSeeder.php
```

Seeder ini menyiapkan tipe hubungan kerja seperti `Permanent`, `Probation`, `Fixed-Term Contract`, `Intern`, `Outsourcing`, `Freelance`, dan `Part Time`. Field operasional yang tersedia:

- `requires_contract_end_date`: apakah tipe kerja wajib punya tanggal akhir kontrak.
- `included_in_payroll`: apakah employee dengan tipe ini masuk payroll rutin.
- `eligible_for_benefits`: apakah tipe ini eligible benefit/allowance.
- `eligible_for_overtime`: apakah tipe ini boleh masuk perhitungan overtime.

Dummy data master HR reference data tersedia di module HR:

```txt
app/Modules/HR/HRReferenceData/Database/Seeders/HRReferenceDataSeeder.php
```

Seeder ini menyiapkan referensi umum seperti `gender`, `marital-status`, `education-level`, `religion`, `blood-type`, dan `bank`. Field utama yang tersedia:

- `category`: kelompok referensi seperti `gender` atau `bank`, dipilih dari category registry agar tidak terjadi salah ketik atau duplikasi nama kategori.
- `code`: kode unik di dalam kategori, misalnya `MALE`, `S1`, atau `BCA`.
- `name`: label yang tampil di dropdown/form HR.

Category registry tersedia sebagai panel collapsible di kanan bawah halaman HR Reference Data. Kategori punya CRUD tersendiri, code kategori bisa diisi manual, dan kategori yang sedang dipakai reference data tidak dapat dihapus. Daftar kategori memakai scroll internal agar panel kanan tidak memanjang saat data kategori bertambah.

Saat add/edit reference data:

- kategori dipilih dari dropdown;
- code reference data diisi manual agar tim HR tetap bisa mengikuti kode internal perusahaan;
- metadata JSON tidak ditampilkan untuk end-user karena sifatnya teknis. Field backend tetap tersedia untuk kebutuhan integrasi di masa depan.

Dummy data master organization structure tersedia di module HR:

```txt
app/Modules/HR/OrganizationStructures/Database/Seeders/HROrganizationStructureSeeder.php
```

Seeder ini membuat root `Company` dan node departement utama dari data `Departements`. Field utama yang tersedia:

- `parent_id`: parent hierarchy; kosong untuk root node.
- `departement_id`: departement terkait untuk node departement/unit.
- `position_id`: position terkait jika node mewakili jabatan.
- `node_type`: jenis node seperti `company`, `division`, `departement`, `unit`, `team`, atau `position`.

Dummy data master employee tersedia di module HR:

```txt
app/Modules/HR/Employees/Database/Seeders/HREmployeeSeeder.php
```

Module `Employees` menyimpan data inti employee yang menjadi pusat relasi untuk Attendance, Payroll, Document Management, CRM, dan module HR berikutnya. Field utama yang tersedia:

- `employee_number`: nomor unik employee untuk payroll, attendance, dan dokumen HR.
- `avatar`: foto employee yang disimpan melalui Spatie Media Library collection `avatar`.
- `user_id`: link opsional ke akun login Console/HR. Satu user hanya boleh terhubung ke satu employee.
- `departement_id`, `position_id`, `job_level_id`, `work_location_id`: relasi struktur kerja dan lokasi utama.
- `employment_status_id`, `employment_type_id`: status kerja dan tipe hubungan kerja.
- `hired_at`, `ended_at`: tanggal mulai dan akhir kerja.
- `supervisor_id`: atasan langsung employee untuk reporting line dan approval awal; tidak boleh menunjuk employee yang sama.
- `date_of_birth`, `place_of_birth`, `national_id`, `address`: profil personal inti. Nomor identitas bersifat sensitif dan unik jika diisi.
- `emergency_contact_name`, `emergency_contact_phone`, `emergency_contact_relation`: kontak darurat employee.
- `active`: status operasional employee.

Detail personal tersebut tetap berada di `Employees` karena merupakan satu profile aggregate. Kontrak, dokumen, dan movement tidak dimasukkan ke tabel ini; ketiganya tetap menjadi module terpisah agar histori dan authorization dapat berkembang tanpa memperbesar aggregate Employees.

## Soft Delete

Module `Departements` memakai soft delete karena departement adalah master organisasi yang akan direferensikan oleh employee, attendance, payroll, approval, dan report.

Aturan delete departement:

- delete mengisi `deleted_at`, bukan menghapus record permanen;
- departement yang masih punya child aktif tidak boleh dihapus;
- query list default hanya menampilkan departement aktif secara data, bukan data yang sudah masuk trash;
- code departement tetap dianggap unik walaupun record lama sudah soft-deleted;
- restore trash bisa ditambahkan nanti saat kebutuhan operasionalnya sudah jelas.

Module HR berikutnya yang menyimpan master atau histori karyawan juga sebaiknya memakai soft delete. Detail prinsip umum tersedia di `docs/architecture/data-lifecycle.md`.

Module `Positions` juga memakai soft delete karena jabatan akan menjadi referensi employee profile, approval flow, attendance, payroll, dan report headcount.

Module `JobLevels` memakai soft delete karena level/grade jabatan akan menjadi referensi employee profile, approval, benefit, payroll, dan report organisasi.

Aturan soft delete JobLevels:

- delete memindahkan job level ke arsip;
- restore tersedia untuk user dengan permission `job-levels.restore` atau `job-levels.manage`;
- force delete tersedia untuk user dengan permission `job-levels.force-delete` atau `job-levels.manage`;
- force delete sebaiknya dipakai hati-hati dan hanya untuk data yang belum dipakai sebagai referensi employee/payroll;
- code job level tetap unik agar integrasi dan histori tidak ambigu.

Module `WorkLocations` memakai soft delete karena lokasi kerja akan menjadi referensi employee profile, attendance area, shift, payroll, dan report organisasi.

Aturan soft delete WorkLocations:

- delete memindahkan lokasi kerja ke arsip;
- restore tersedia untuk user dengan permission `work-locations.restore` atau `work-locations.manage`;
- force delete tersedia untuk user dengan permission `work-locations.force-delete` atau `work-locations.manage`;
- force delete sebaiknya hanya dipakai untuk lokasi yang belum dipakai employee, attendance, atau payroll;
- timezone wajib valid karena dipakai sebagai basis waktu attendance dan scheduler.
- latitude dan longitude nullable untuk lokasi remote, tetapi lokasi fisik sebaiknya punya koordinat valid.
- `geofence_radius_meters` nullable untuk lokasi remote, tetapi lokasi fisik yang akan dipakai Attendance sebaiknya punya radius toleransi dalam meter.
- map selector memakai konfigurasi Google Maps dari Console System Settings.

Konfigurasi Google Maps dikelola dari Console:

```txt
Console > System Settings > Google Maps
```

Field yang dipakai:

- Google Maps API Key untuk memuat Google Maps JavaScript API.
- Google Maps Map ID untuk styling map/vector map jika tersedia.

Module `EmploymentStatuses` memakai soft delete karena status kerja akan menjadi referensi employee profile, attendance, payroll, report headcount, dan histori lifecycle.

Aturan soft delete EmploymentStatuses:

- delete memindahkan employment status ke arsip;
- restore tersedia untuk user dengan permission `employment-statuses.restore` atau `employment-statuses.manage`;
- force delete tersedia untuk user dengan permission `employment-statuses.force-delete` atau `employment-statuses.manage`;
- force delete sebaiknya hanya dipakai untuk status yang belum dipakai employee;
- status akhir seperti `RESIGNED` dan `TERMINATED` sebaiknya tidak mewajibkan attendance dan tidak masuk payroll berjalan.

Module `EmploymentTypes` memakai soft delete karena tipe hubungan kerja akan menjadi referensi employee profile, kontrak, benefit, attendance overtime, payroll, dan report HR.

Aturan soft delete EmploymentTypes:

- delete memindahkan employment type ke arsip;
- restore tersedia untuk user dengan permission `employment-types.restore` atau `employment-types.manage`;
- force delete tersedia untuk user dengan permission `employment-types.force-delete` atau `employment-types.manage`;
- force delete sebaiknya hanya dipakai untuk tipe kerja yang belum dipakai employee atau kontrak;
- tipe kerja dengan `requires_contract_end_date` wajib memaksa tanggal akhir kontrak saat module employee/contract tersedia.

Module `HRReferenceData` memakai soft delete karena referensi umum akan menjadi pilihan employee profile, dokumen, report, dan integrasi lintas project.

Aturan soft delete HRReferenceData:

- delete memindahkan reference data ke arsip;
- restore tersedia untuk user dengan permission `hr-reference-data.restore` atau `hr-reference-data.manage`;
- force delete tersedia untuk user dengan permission `hr-reference-data.force-delete` atau `hr-reference-data.manage`;
- force delete sebaiknya hanya dipakai untuk reference data yang belum dipakai employee, dokumen, payroll, atau report;
- kombinasi `category + code` wajib unik agar aman dipakai lintas modul.
- category registry memakai soft delete internal;
- category registry tidak boleh dihapus jika masih dipakai oleh reference data aktif maupun archived.

Module `OrganizationStructures` memakai soft delete karena hierarchy organisasi akan menjadi referensi employee assignment, reporting line, approval Attendance/Payroll, dan report organisasi.

Aturan soft delete OrganizationStructures:

- delete memindahkan structure ke arsip;
- structure yang masih punya child aktif tidak boleh diarsipkan;
- restore tersedia untuk user dengan permission `organization-structures.restore` atau `organization-structures.manage`;
- force delete tersedia untuk user dengan permission `organization-structures.force-delete` atau `organization-structures.manage`;
- force delete tidak boleh dilakukan jika structure masih punya child, termasuk child yang sudah diarsipkan;
- parent tidak boleh circular atau menunjuk diri sendiri/turunannya.

Module `Employees` memakai soft delete karena employee adalah master historis yang akan dipakai attendance, payroll, documents, approval, dan audit lintas project.

Aturan soft delete Employees:

- delete memindahkan employee ke arsip;
- restore tersedia untuk user dengan permission `employees.restore` atau `employees.manage`;
- force delete tersedia untuk user dengan permission `employees.force-delete` atau `employees.manage`;
- force delete sebaiknya hanya dipakai untuk employee yang belum dipakai attendance, payroll, document, atau transaksi lintas project;
- `employee_number` tetap unik agar histori dan integrasi tidak ambigu;
- `user_id` hanya boleh terhubung ke satu employee.

Catatan backend test:

- `tests/Feature/HRWorkLocationTest.php` menutup validasi latitude, longitude, dan `geofence_radius_meters`.
- `tests/Feature/SystemSettingTest.php` menutup penyimpanan konfigurasi Google Maps.
- `tests/Feature/HREmployeeTest.php` menutup create, update, soft delete, restore, dan force delete Employees.

## Akses Dari Welcome

Project HR bisa dibuka dari kartu `HR` di welcome page.

Alur akses:

1. User klik kartu `HR`.
2. Jika belum login, user diarahkan ke halaman login HR di `/hr/login`.
3. Setelah login berhasil, user masuk ke `/hr/dashboard`.
4. Dari dashboard HR, user bisa membuka module aktif seperti `/hr/departements`.

Catatan: HR memiliki halaman login dan dashboard sendiri agar pemisahan project lebih jelas. Secara backend, guard/session tetap memakai auth Laravel yang sama supaya audit login, security policy, password reset, dan account management tetap konsisten.

## Route Dan Frontend

Semua module HR memakai prefix project:

```txt
/hr/{module-slug}
```

Contoh yang sudah ada:

```txt
Login      : /hr/login
Dashboard  : /hr/dashboard
Route      : /hr/departements
Route name : hr.departements.*
Frontend  : resources/js/pages/hr/departements/index.tsx
Backend   : app/Modules/HR/Departements

Route      : /hr/positions
Route name : hr.positions.*
Frontend  : resources/js/pages/hr/positions/index.tsx
Backend   : app/Modules/HR/Positions

Route      : /hr/job-levels
Route name : hr.job-levels.*
Frontend  : resources/js/pages/hr/job-levels/index.tsx
Backend   : app/Modules/HR/JobLevels

Route      : /hr/work-locations
Route name : hr.work-locations.*
Frontend  : resources/js/pages/hr/work-locations/index.tsx
Backend   : app/Modules/HR/WorkLocations

Route      : /hr/employment-types
Route name : hr.employment-types.*
Frontend  : resources/js/pages/hr/employment-types/index.tsx
Backend   : app/Modules/HR/EmploymentTypes

Route      : /hr/hr-reference-data
Route name : hr.hr-reference-data.*
Frontend  : resources/js/pages/hr/hr-reference-data/index.tsx
Backend   : app/Modules/HR/HRReferenceData

Route      : /hr/organization-structures
Route name : hr.organization-structures.*
Frontend  : resources/js/pages/hr/organization-structures/index.tsx
Backend   : app/Modules/HR/OrganizationStructures
```

Generator yang dipakai:

```bash
php artisan make:module Departements --project=HR
```

Untuk module berikutnya:

```bash
php artisan make:module Positions --project=HR
php artisan make:module JobLevels --project=HR
php artisan make:module WorkLocations --project=HR
php artisan make:module EmploymentStatuses --project=HR
php artisan make:module EmploymentTypes --project=HR
php artisan make:module HRReferenceData --project=HR
php artisan make:module OrganizationStructures --project=HR
php artisan make:module Employees --project=HR
```

Catatan: `HR` adalah acronym uppercase, tetapi slug frontend dan route tetap `hr`, bukan `h-r`.

## Urutan Pengerjaan Yang Disarankan

Mulai dari data master organisasi:

1. `Departements`
2. `Positions`
3. `JobLevels`
4. `WorkLocations`
5. `EmploymentStatuses`
6. `EmploymentTypes`
7. `HRReferenceData`
8. `OrganizationStructures`

Setelah itu baru masuk ke data employee:

1. `Employees`
2. `EmployeeProfiles`
3. `EmployeeContacts`
4. `EmployeeIdentities`
5. `EmployeeDocuments`
6. `EmployeeContracts`
7. `EmployeeMovements`

`EmployeeMovements` vertical slice pertama tersedia untuk transfer efektif hari ini. Module menyimpan snapshot assignment sebelum/sesudah dan menjadi satu-satunya jalur pada slice ini yang menerapkan perubahan department, position, work location, dan supervisor ke profile Employees. Lihat [spesifikasi Employee Movements](employee-movements/specification.md) dan [ADR effective-dated movement](employee-movements/decisions/001-effective-dated-movements.md).

## Contract Wajib Per Module HR

Setiap module HR wajib punya:

- `module.php`
- `routes.php`
- `permissions.php`
- `navigation.php`
- `Providers/*ServiceProvider.php`
- `DTO/`
- `Http/Controllers/`
- `Http/Requests/`
- `Policies/`
- `Services/`
- `Transactions/`
- `Support/`

Untuk module yang menyimpan data, tambahkan:

- `Models/`
- migration table dengan prefix `hr_`
- soft delete untuk data master/histori
- audit log untuk create, update, delete
- soft delete untuk delete data master
- feature test untuk view, create, update, delete, dan validasi penting

## Naming Convention

Backend:

```txt
App\\Modules\\HR\\Departements
App\Modules\HR\Positions
App\Modules\HR\Employees
```

Frontend:

```txt
resources/js/pages/hr/departements
resources/js/pages/hr/positions
resources/js/pages/hr/employees
```

Database:

```txt
hr_departements
hr_positions
hr_employees
```

Permission:

```txt
hr.view
departements.view
departements.create
departements.update
departements.delete
departements.manage
```

Untuk module berikutnya gunakan pola yang sama:

```txt
positions.view
positions.create
positions.update
positions.delete
positions.manage
```

Untuk `Positions`, permission awal:

```txt
positions.view
positions.create
positions.update
positions.delete
positions.manage
```

Untuk `JobLevels`, permission awal:

```txt
job-levels.view
job-levels.create
job-levels.update
job-levels.delete
job-levels.restore
job-levels.force-delete
job-levels.manage
```

Untuk `WorkLocations`, permission awal:

```txt
work-locations.view
work-locations.create
work-locations.update
work-locations.delete
work-locations.restore
work-locations.force-delete
work-locations.manage
```

Untuk `EmploymentTypes`, permission awal:

```txt
employment-types.view
employment-types.create
employment-types.update
employment-types.delete
employment-types.restore
employment-types.force-delete
employment-types.manage
```

Untuk `HRReferenceData`, permission awal:

```txt
hr-reference-data.view
hr-reference-data.create
hr-reference-data.update
hr-reference-data.delete
hr-reference-data.restore
hr-reference-data.force-delete
hr-reference-data.manage
```

Untuk `OrganizationStructures`, permission awal:

```txt
organization-structures.view
organization-structures.create
organization-structures.update
organization-structures.delete
organization-structures.restore
organization-structures.force-delete
organization-structures.manage
```

## UI/UX

UI HR mengikuti pola Console:

- Summary cards di bagian atas.
- Workspace utama memakai tabel, filter, dan pagination bar.
- Form create/update di panel kanan atau dialog sesuai kompleksitas.
- Delete memakai dialog konfirmasi.
- Field wajib diberi tanda `*`.
- Status aktif/nonaktif memakai badge.
- Empty state harus memberi konteks yang jelas.

Standar UI/UX terbaru untuk module master HR:

- Shortcut keyboard ditampilkan sebagai panel collapsible agar halaman tetap ringkas.
- Header tabel dan deskripsi dibuat rapat, tidak boros ruang vertikal.
- Search field dibuat compact dan tidak mengambil lebar berlebihan.
- Dropdown default memakai label pendek seperti `Status` atau `Departements`.
- Tabel tidak boleh memaksa horizontal scroll jika informasi masih bisa diringkas.
- Row utama hanya menampilkan informasi paling penting.
- Kode data harus eksplisit, misalnya `Kode: HRD`, bukan badge kode tanpa konteks.
- Informasi relasi harus eksplisit, misalnya `Kode parent: HRD` atau `Kode departement: HRD`.
- Angka mentah harus diberi konteks, misalnya `Memiliki 2 sub-departement`, bukan hanya `2`.
- Field `sort_order` tidak ditampilkan ke user biasa.
- Urutan sementara diisi otomatis dari backend memakai nomor berikutnya.
- Jika ordering perlu dikelola user, gunakan drag-and-drop ordering, bukan input angka manual.
- Panel kanan harus memakai bahasa yang jelas untuk pengguna awam, bukan istilah teknikal mentah.
- Deskripsi di form memakai textarea, bukan input satu baris.
- Untuk module lokasi, form create/update berada di atas directory dan panel kanan tetap menjadi preview read-only dengan field disabled.
- Field koordinat lokasi dikelola lewat map selector; input latitude/longitude tetap tersedia untuk koreksi manual.

Untuk module besar seperti `Employees`, pecah halaman menjadi:

```txt
resources/js/pages/hr/employees/
  index.tsx
  types.ts
  employees-components/
    employee-header.tsx
    employee-summary-cards.tsx
    employee-table.tsx
    employee-form.tsx
    delete-employee-dialog.tsx
```

## Integrasi Ke Project Lain

HR adalah upstream untuk Attendance dan Payroll. Project lain tidak boleh langsung membaca model internal HR tanpa contract.

Gunakan:

- Shared Kernel untuk value object lintas project.
- Domain Event untuk perubahan employee/organization.
- Integration Layer untuk adapter/projector.

Contoh event awal:

```txt
DepartementCreated
PositionCreated
EmployeeCreated
EmployeeUpdated
EmployeeTransferred
EmploymentTerminated
```

## Checklist Saat Menambah Module HR

- Route memakai prefix `/hr`.
- Route name memakai prefix `hr.`.
- Navigation masuk group `HR`.
- Permission masuk `permissions.php`.
- Policy terdaftar di provider module.
- Mutasi memakai FormRequest, DTO, Service, dan Transaction.
- Audit log aktif untuk aksi penting.
- UI mengikuti pola Console.
- Test feature tersedia.
- Jika ada perubahan arsitektur atau generator, update dokumen terkait.
