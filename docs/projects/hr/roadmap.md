# Roadmap Project HR

Roadmap ini adalah rencana awal untuk membangun project `HR` di atas starterkit modular. Fokusnya adalah menjadi sumber kebenaran untuk data karyawan, struktur organisasi, status kerja, dokumen karyawan, dan lifecycle employee. Project ini sebaiknya dibangun sebelum `Attendance` dan `Payroll`, karena keduanya membutuhkan data employee yang stabil.

Panduan teknis module HR tersedia di `docs/projects/hr/module-guide.md`. Untuk pengguna pemula, baca [Ebook Panduan Penggunaan Modul HR](user-guide/panduan-penggunaan-modul-hr.pdf) atau [sumber HTML-nya](user-guide/panduan-penggunaan-modul-hr.html).

## Status Implementasi

Module yang sudah tersedia:

- `departements`: master departement, route `/hr/departements`, frontend `resources/js/pages/hr/departements`, backend `app/Modules/HR/Departements`. **Status: tersedia.**
- `departements` sudah memakai soft delete untuk menjaga histori struktur organisasi.
- `positions`: master jabatan, route `/hr/positions`, frontend `resources/js/pages/hr/positions`, backend `app/Modules/HR/Positions`. **Status: tersedia.**
- `positions` sudah memakai soft delete dan terhubung ke `departements`.
- `job-levels`: master level/grade jabatan, route `/hr/job-levels`, frontend `resources/js/pages/hr/job-levels`, backend `app/Modules/HR/JobLevels`. **Status: tersedia.**
- `job-levels` sudah memakai soft delete, restore, dan force delete berbasis permission.
- `work-locations`: master lokasi kerja, route `/hr/work-locations`, frontend `resources/js/pages/hr/work-locations`, backend `app/Modules/HR/WorkLocations`. **Status: tersedia.**
- `work-locations` sudah memakai soft delete, restore, dan force delete berbasis permission.
- `work-locations` sudah menyimpan latitude/longitude, radius geofence dalam meter, dan memiliki map selector yang memakai konfigurasi Google Maps dari System Settings.
- `employment-statuses`: master status kerja, route `/hr/employment-statuses`, frontend `resources/js/pages/hr/employment-statuses`, backend `app/Modules/HR/EmploymentStatuses`. **Status: tersedia.**
- `employment-statuses` sudah memakai soft delete, restore, force delete, permission modular, dan field operasional untuk attendance/payroll/status akhir.
- `employment-types`: master tipe hubungan kerja, route `/hr/employment-types`, frontend `resources/js/pages/hr/employment-types`, backend `app/Modules/HR/EmploymentTypes`. **Status: tersedia.**
- `employment-types` sudah memakai soft delete, restore, force delete, permission modular, dan field operasional untuk kontrak, benefit, overtime, dan payroll.
- `hr-reference-data`: master referensi umum HR, route `/hr/hr-reference-data`, frontend `resources/js/pages/hr/hr-reference-data`, backend `app/Modules/HR/HRReferenceData`. **Status: tersedia.**
- `hr-reference-data` sudah memakai soft delete, restore, force delete, permission modular, dan kategori referensi untuk gender, marital status, education level, religion, bank, dan blood type.
- `organization-structures`: master hierarchy organisasi, route `/hr/organization-structures`, frontend `resources/js/pages/hr/organization-structures`, backend `app/Modules/HR/OrganizationStructures`. **Status: tersedia.**
- `organization-structures` sudah memakai soft delete, restore, force delete, permission modular, parent-child hierarchy, relasi departement/position, dan proteksi child aktif saat delete.

## Prinsip Utama

- HR menjadi master data utama untuk employee.
- User login di `Console` tidak sama dengan employee profile di `HR`.
- Semua perubahan data penting karyawan harus punya audit trail.
- Data master dan histori HR memakai soft delete agar aman untuk restore dan integrasi.
- Struktur organisasi harus mendukung approval di Attendance dan Payroll.
- Integrasi ke Attendance dan Payroll memakai event/contract, bukan akses internal sembarangan.

## Departements vs OrganizationStructures

`Departements` adalah master unit kerja seperti `Human Resources`, `Finance`, `Operations`, atau `IT`. Data ini dipakai untuk menjawab employee berada di departement apa, position berada di unit mana, serta report/headcount/budget per departement.

`OrganizationStructures` adalah peta hierarchy resmi organisasi. Data ini dipakai untuk menjawab node mana berada di bawah node mana, jalur reporting line, posisi node di organization chart, dan jalur approval untuk modul seperti Attendance dan Payroll.

Contoh:

```txt
Company
└── Human Resources
    ├── Recruitment Team
    ├── Training & Development
    └── Payroll Administration
```

Pada contoh di atas, `Human Resources` adalah data `Departements`, sedangkan susunan `Company -> Human Resources -> Recruitment Team` adalah data `OrganizationStructures`.

Keduanya dipisah karena realita organisasi tidak selalu cukup diwakili oleh master departement. Satu departement bisa memiliki beberapa team/sub-unit untuk approval, reporting line bisa lintas departement, dan node tertentu bisa mewakili position tertentu dalam hierarchy.

## Struktur Project Awal

```txt
app/Modules/HR/
  Employees/
  Departements/
  Positions/
  JobLevels/
  WorkLocations/
  EmploymentTypes/
  EmploymentStatuses/
  HRReferenceData/
  OrganizationStructures/
  EmployeeDocuments/
  EmployeeContracts/
  EmployeeMovements/
  Onboardings/
  Offboardings/
```

Frontend:

```txt
resources/js/pages/hr/
  employees/
  departements/
  positions/
  job-levels/
  work-locations/
  employment-types/
  employment-statuses/
  hr-reference-data/
  organization-structures/
  employee-documents/
  employee-contracts/
  employee-movements/
  onboardings/
  offboardings/
```

## Phase 1: Organization Foundation

Target: struktur organisasi dasar siap dipakai.

Module:

- `departements` **Status: tersedia.**
- `Positions` **Status: tersedia.**
- `JobLevels` **Status: tersedia.**
- `WorkLocations` **Status: tersedia.**
- `EmploymentStatuses` **Status: tersedia.**
- `EmploymentTypes` **Status: tersedia.**
- `HRReferenceData` **Status: tersedia.**
- `OrganizationStructures` **Status: tersedia.**

Fitur:

- Departement master. **Status: tersedia.**
- Position/job title master. **Status: tersedia.**
- Job level/grade master. **Status: tersedia.**
- Work location master. **Status: tersedia.**
- Work location coordinate picker untuk menyimpan titik lokasi kantor/cabang. **Status: tersedia.**
- Work location geofence radius untuk batas toleransi attendance check-in/check-out. **Status: tersedia.**
- Employment type master: permanent, contract, intern, outsourcing, freelance. **Status: tersedia.**
- Employment type operational flags: wajib tanggal akhir kontrak, masuk payroll, eligible benefit, dan eligible overtime.
- Employment status master: probation, permanent, contract, intern, resigned. **Status: tersedia.**
- Employment status operational flags: wajib attendance, masuk payroll, dan status akhir.
- HR reference data master: gender, marital status, education level, religion, bank, blood type. **Status: tersedia.**
- HR reference data category registry: kategori punya CRUD sendiri dalam panel collapsible, dipilih dari dropdown, code bisa diisi manual, dan kategori yang sedang dipakai tidak bisa dihapus. **Status: tersedia.**
- Organization chart sederhana. **Status: tersedia sebagai hierarchy table/panel.**
- Supervisor/manager relationship.

Validasi penting:

- Departement code unik.
- Position wajib punya Departement aktif.
- Job level tidak boleh duplicate dalam grade yang sama.
- Organization parent tidak boleh menjadi child dari dirinya sendiri.
- Work location yang sudah dipakai employee tidak boleh dihapus langsung.
- Work location yang dipakai attendance/geofence harus punya koordinat yang valid.
- Work location fisik yang dipakai attendance geofence sebaiknya punya `geofence_radius_meters`; lokasi remote boleh kosong.
- Employment type yang sudah dipakai employee tidak boleh force delete.
- Employment type dengan flag wajib end date harus membuat kontrak employee punya tanggal akhir saat module employee/contract tersedia.
- Employment status yang sudah dipakai employee tidak boleh force delete.
- Employment status akhir seperti resigned/terminated tidak masuk attendance dan payroll berjalan.
- HR reference data yang sudah dipakai employee tidak boleh force delete.
- Reference key harus unik per kategori agar aman dipakai lintas modul.
- Reference category wajib terdaftar di registry sebelum dipakai reference data.
- Reference category yang sedang dipakai reference data tidak boleh dihapus.
- Organization parent tidak boleh circular.
- Organization structure yang masih punya child aktif tidak boleh diarsipkan.
- Organization structure yang masih punya child tidak boleh force delete.

## Phase 2: Employee Core

Target: employee profile menjadi master data utama.

Module:

- `Employees` **Status: tersedia.**
- `EmployeeProfiles`
- `EmployeeContacts`
- `EmployeeIdentities`

Fitur:

- Employee number auto/manual. **Status: tersedia untuk input manual.**
- Employee avatar/photo dengan Media Library. **Status: tersedia.**
- Link employee ke `Console\User` jika butuh self-service. **Status: tersedia.**
- Work data: departement, position, job level, work location, employment status, dan employment type. **Status: tersedia.**
- Soft delete, restore, dan force delete employee dengan permission terpisah. **Status: tersedia.**
- Personal data.
- Contact information.
- Emergency contact.
- Identity information.

Validasi penting:

- Employee number unik.
- Email kerja unik jika digunakan.
- User login hanya boleh terhubung ke satu employee aktif.
- Employee wajib punya employment status.
- Supervisor tidak boleh menunjuk dirinya sendiri.
- Employee yang sudah dipakai attendance, payroll, document, atau transaksi lintas project tidak boleh force delete.

Event awal:

- `EmployeeCreated`
- `EmployeeUpdated`
- `EmployeeLinkedToUser`
- `EmployeeSupervisorChanged`

## Phase 3: Documents & Contracts

Target: dokumen dan kontrak karyawan bisa dikelola.

Module:

- `EmployeeDocuments`
- `EmployeeContracts`
- `HRReferenceData` category `EMPLOYEE_DOCUMENT_TYPE`

Rancangan Employee Documents kini dipisahkan tegas dari storage engine: HR memiliki metadata, expiry, dan verification; Document Management memiliki file/version/access/retention. Lihat [paket spesifikasi Employee Documents](employee-documents/README.md).

Fitur:

- Hubungkan metadata dokumen karyawan ke file Document Management melalui reference contract; jangan membuat media collection dokumen kedua di HR.
- Document type: KTP, NPWP, contract, certificate, medical, other.
- Contract record: start date, end date, status.
- Contract reminder.
- Document expiry reminder.
- Download document sesuai permission.

Validasi penting:

- Dokumen private hanya bisa diakses role tertentu.
- Contract active tidak boleh overlap untuk employee yang sama jika policy melarang.
- Dokumen dengan expiry wajib punya tanggal expired.
- Delete dokumen harus masuk audit log.

## Phase 4: Employee Movement

Target: perubahan organisasi karyawan tercatat historis.

Status 2026-07-13: vertical slice transfer DRAFT → before/after history → atomic apply efektif hari ini telah tersedia. Promotion, demotion, status change, future scheduling, dan approval tetap menjadi pekerjaan lanjutan. Lihat [Employee Movements](employee-movements/README.md).

Module:

- `EmployeeMovements`
- `Promotions`
- `Transfers`
- `StatusChanges`

Fitur:

- Promotion.
- Transfer departement/location.
- Position change.
- Supervisor change.
- Employment status change.
- Effective date.
- Movement history.
- Approval movement optional.

Validasi penting:

- Effective date wajib.
- Movement tidak boleh membuat employee berada pada dua posisi aktif di tanggal yang sama.
- Movement backdate harus punya permission khusus.
- Perubahan supervisor harus menjaga hierarchy tidak circular.

Event awal:

- `EmployeePromoted`
- `EmployeeTransferred`
- `EmployeeStatusChanged`
- `EmployeePositionChanged`

## Phase 5: Onboarding & Offboarding

Target: lifecycle masuk dan keluar karyawan bisa dikontrol.

Status 2026-07-15: MVP `Onboardings` telah tersedia dari template snapshot, draft/activation, task lifecycle, completion/cancellation, filter operasional, archive/restore, hingga overdue command. Baca [paket project Onboardings](onboardings/README.md) dan [frontend quality review](onboardings/07-frontend-quality-task12.md). Integration contract tetap deferred sampai ada consumer nyata.

Status 2026-07-16: paket specification dan ADR `Offboardings` telah diterima. Task 01–02 menyediakan module/state/permission contract serta template ordered checklist create/list yang policy-protected. Baca [paket project Offboardings](offboardings/README.md).

Module:

- `Onboardings` **Status: MVP tersedia.**
- `Offboardings` **Status: template slice tersedia; berikutnya archive/restore pada Task 03.**
- `ChecklistTemplates`
- `ChecklistTasks`

Fitur:

- Onboarding checklist.
- Assign onboarding task.
- Offboarding checklist.
- Exit reason.
- Handover checklist.
- Asset/document return placeholder.
- Completion tracking.

Validasi penting:

- Employee baru bisa punya onboarding aktif satu kali per employment period.
- Offboarding harus punya exit date dan reason.
- Employee terminated tidak boleh dipakai untuk attendance/payroll period setelah effective date.
- Offboarding completion harus tercatat.

Event kandidat berikut belum dipublikasikan dan menunggu approval consumer:

- `EmployeeOnboardingStarted`
- `EmployeeOnboardingCompleted`
- `EmployeeOffboardingStarted`
- `EmploymentTerminated`

## Phase 6: HR Reports

Target: HR bisa membaca data organisasi dan employee.

Module:

- `HRReports`
- `HeadcountReports`
- `TurnoverReports`

Fitur:

- Headcount by Departement.
- Headcount by location.
- Employment status summary.
- New hire report.
- Resignation/termination report.
- Contract expiry report.
- Document expiry report.
- Export Excel/PDF.

Catatan:

- Export besar masuk queue.
- Report sebaiknya membaca snapshot/reporting query jika data membesar.

## Phase 7: Integration Readiness

Target: HR menjadi upstream untuk Attendance dan Payroll.

Module kandidat:

- `EmployeeSnapshots`
- `HRIntegrations`
- `EmployeeEventProjectors`

Fitur:

- Publish employee snapshot.
- Sync employee to Attendance.
- Sync employee payroll profile placeholder.
- Employee status lock for Attendance/Payroll.
- Integration event log.

Integrasi event:

- HR dispatch `EmployeeCreated`.
- HR dispatch `EmployeeUpdated`.
- HR dispatch `EmployeeTransferred`.
- HR dispatch `EmploymentTerminated`.
- Attendance listen event untuk membuat/memperbarui attendance employee read model.
- Payroll listen event untuk membuat/memperbarui payroll employee read model.

## Permission Awal

Contoh permission:

```txt
hr.view
hr.dashboard.view
employees.view
employees.create
employees.update
employees.delete
employees.link-user
employees.view-sensitive
Departements.view
Departements.manage
positions.view
positions.manage
job-levels.view
job-levels.create
job-levels.update
job-levels.delete
job-levels.restore
job-levels.force-delete
job-levels.manage
work-locations.view
work-locations.create
work-locations.update
work-locations.delete
work-locations.restore
work-locations.force-delete
work-locations.manage
employment-types.view
employment-types.create
employment-types.update
employment-types.delete
employment-types.restore
employment-types.force-delete
employment-types.manage
employment-statuses.view
employment-statuses.manage
hr-reference-data.view
hr-reference-data.create
hr-reference-data.update
hr-reference-data.delete
hr-reference-data.restore
hr-reference-data.force-delete
hr-reference-data.manage
organization-structures.view
organization-structures.create
organization-structures.update
organization-structures.delete
organization-structures.restore
organization-structures.force-delete
organization-structures.manage
employee-documents.view
employee-documents.upload
employee-documents.download
employee-documents.delete
employee-contracts.view
employee-contracts.manage
employee-movements.view
employee-movements.create
employee-movements.approve
onboardings.view
onboardings.manage
offboardings.view
offboardings.manage
hr-reports.view
hr-reports.export
```

Role awal:

- `hr-admin`: akses penuh HR.
- `hr-manager`: manage employee, movement, onboarding, offboarding, report.
- `hr-officer`: create/update employee dan dokumen sesuai batasan.
- `supervisor`: read team profile dan approval tertentu.
- `employee`: read profile miliknya jika self-service aktif.
- `hr-viewer`: read-only data HR sesuai permission.

Catatan implementasi starterkit:

- `hr-manager`, `hr-officer`, dan `hr-viewer` mengambil default permission dari `ModulePermissionRegistry`.
- Setiap module HR harus menambahkan mapping role tersebut di `Support/Permissions.php`.
- `hr-officer` minimal mendapat permission `view`, `create`, dan `update` untuk master data operasional yang boleh diedit.

## UI/UX Arah Awal

HR sebaiknya terasa rapi, human-centered, dan mudah discan:

- Dashboard headcount, new hires, contract expiry, document expiry.
- Employee table dengan filter departement, location, status, job level.
- Detail panel kanan untuk preview employee.
- Work location form menampilkan map selector di atas directory, sedangkan panel kanan tetap read-only preview.
- Employee profile tab: personal, work, documents, contracts, movements.
- Organization chart atau tree untuk structure view.
- Badge status: Active, Probation, Contract, Intern, Resigned, Terminated.
- Quick action: add employee, upload document, create movement, start onboarding.
- Shortcut keyboard untuk search, create employee, fokus table, buka detail.

## Urutan Implementasi Yang Disarankan

1. `php artisan make:module Departements --project=HR`
2. `php artisan make:module Positions --project=HR`
3. `php artisan make:module JobLevels --project=HR`
4. `php artisan make:module WorkLocations --project=HR`
5. `php artisan make:module EmploymentTypes --project=HR`
6. `php artisan make:module EmploymentStatuses --project=HR`
7. `php artisan make:module HRReferenceData --project=HR`
8. `php artisan make:module OrganizationStructures --project=HR`
9. `php artisan make:module Employees --project=HR`
10. Lengkapi core profile `Employees`: personal, alamat, identitas, kontak darurat, dan supervisor. **Selesai 2026-07-12.**
11. `php artisan make:module EmployeeDocuments --project=HR`
12. `php artisan make:module EmployeeContracts --project=HR`
13. `php artisan make:module EmployeeMovements --project=HR`
14. `php artisan make:module Onboardings --project=HR`
15. `php artisan make:module Offboardings --project=HR`
16. `php artisan make:module HRReports --project=HR`

## Definition of Done Per Module

- Route, permission, navigation, provider tersedia.
- Policy terpasang.
- FormRequest tersedia untuk aksi mutasi.
- DTO dipakai untuk input service.
- Service berisi use case.
- Transaction membungkus write operation.
- Test route dan permission tersedia.
- Test operasi utama tersedia.
- Audit log untuk data sensitif dan movement tersedia.
- Domain event tersedia untuk perubahan employee penting.
- Integration adapter/projector tersedia jika data dipakai Attendance/Payroll.
- UI sudah dipisah menjadi komponen.
- Empty state, loading state, dan error state tersedia.

## Risiko Yang Perlu Dijaga

- Data employee bercampur dengan user login Console.
- Perubahan departement/position tidak punya history.
- Supervisor hierarchy circular.
- Employee terminated masih aktif di Attendance/Payroll.
- Dokumen private bisa diakses tanpa permission.
- Payroll membaca data employee terbaru, bukan snapshot yang berlaku pada periode payroll.
- Attendance membaca employee tanpa status employment yang valid.

## Hubungan Dengan Project Lain

```txt
HR
  -> Attendance
  -> Payroll
  -> Accounting
```

HR menyediakan master employee. Attendance memakai employee untuk schedule dan presensi. Payroll memakai employee dan attendance snapshot untuk perhitungan gaji. Accounting menerima hasil payroll sebagai journal atau liability.

Rule penting:

- Attendance tidak boleh menjadi sumber utama employee.
- Payroll tidak boleh mengambil data employee langsung dari internal model HR tanpa snapshot/contract.
- Accounting tidak perlu tahu detail personal employee, cukup menerima journal/payroll summary.
