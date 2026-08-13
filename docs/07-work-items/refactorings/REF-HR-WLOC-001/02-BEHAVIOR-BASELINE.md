---
id: DOC-REF-HR-WLOC-001-BASELINE
title: Baseline Perilaku HR WorkLocations
document_type: behavior-baseline
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, TESTING-STRATEGY]
---

# Baseline Perilaku HR WorkLocations

## Tujuan

Merekam perilaku yang harus tetap sama setelah refactor. Kode adalah bukti tentang apa yang dilakukan aplikasi sekarang; baseline ini tidak mengesahkan perilaku baru atau nilai bisnis yang belum diputuskan.

## Permukaan HTTP

Seluruh route memakai interface web/session dan prefix hr/work-locations.

| Method | URI | Nama | Operasi |
|---|---|---|---|
| GET | hr/work-locations | hr.work-locations.index | daftar, filter, summary, dan map settings |
| POST | hr/work-locations | hr.work-locations.store | membuat lokasi |
| PUT | hr/work-locations/{workLocation} | hr.work-locations.update | memperbarui lokasi |
| DELETE | hr/work-locations/{workLocation} | hr.work-locations.destroy | soft delete |
| DELETE | hr/work-locations/{workLocation}/force | hr.work-locations.force-destroy | force delete |
| PATCH | hr/work-locations/{workLocation}/restore | hr.work-locations.restore | restore |

Controller memakai middleware can:viewAny, can:create, can:update, can:delete, can:restore, dan can:forceDelete terhadap WorkLocation atau parameter route workLocation. Route model binding dan pemakaian withTrashed pada operasi arsip wajib tetap sama.

## Authorization dan Permission

Permission aktif yang terkait adalah hr.view serta work-locations.view, create, update, delete, restore, force-delete, dan manage. Policy menghubungkan operasi controller ke Laravel authorization/Spatie dan model User. Policy bukan aturan domain murni sehingga targetnya Presentation/Policies.

File permissions.php, navigation.php, dan Support/Permissions.php tidak diubah pada pilot. Duplikasi sumber export permission yang teramati tidak diselesaikan diam-diam.

## Data dan Perilaku Aplikasi

- Model Eloquent menggunakan tabel hr_work_locations, SoftDeletes, fillable dan casts yang ada.
- Migration memakai identifier bigint saat ini. Tidak ada migration atau data transformation dalam scope.
- Employee memiliki relasi belongsTo melalui work_location_id; foreign key yang ada tetap berlaku.
- Request store/update mempertahankan aturan validasi serta normalisasi input saat ini.
- DTO mempertahankan default, transformasi, dan normalisasi kode saat ini.
- Service/transaction mempertahankan create, update, delete, restore, dan force-delete.
- Index mempertahankan filter search, status, archive, city, per_page, summary, serta mapSettings.
- Respons halaman tetap resources/js/pages/hr/work-locations/index melalui Inertia.
- Manifest module.php tetap mengekspor route, permission, navigation, event metadata, listener, integration metadata, dan dependency yang sama; hanya namespace provider yang direncanakan berubah.

## Audit dan Dependensi

Operasi tetap menggunakan AuditLogService dan SystemSettingService. Identifier modul audit tetap hr.work-locations. Nama event audit yang harus tetap adalah:

- WorkLocation.created
- WorkLocation.updated
- WorkLocation.deleted
- WorkLocation.restored
- WorkLocation.force-deleted

Pilot tidak mengubah semantics integrasi tersebut.

## Consumer yang Teridentifikasi

Consumer produksi model WorkLocation ditemukan pada:

- app/Http/Controllers/Console/DashboardController.php
- app/Modules/HR/EmployeeMovements/Services/EmployeeMovementsService.php
- app/Modules/HR/Employees/Database/Seeders/HREmployeeSeeder.php
- app/Modules/HR/Employees/Models/Employee.php
- app/Modules/HR/Employees/Services/EmployeesService.php
- app/Modules/HR/HRReports/Database/Seeders/HRReportLifecycleSeeder.php
- app/Modules/HR/WorkLocations/Database/Seeders/HRWorkLocationSeeder.php
- class internal WorkLocations yang memakai model, DTO, service, transaction, policy, request, atau provider.

Test lintas sistem yang mereferensikan WorkLocation tetap di tests/Feature, termasuk HREmployeeMovementTest, HREmployeeTest, HRIntegrationAssignmentSnapshotTest, HRReportCommandTest, dan HRReportHeadcountTest. Import-nya tetap harus diperbarui ketika namespace model dipindahkan.

## Bukti Baseline

Pada 2026-08-14 sebelum perubahan kode:

- php artisan module:validate HR.WorkLocations --json: exit 0 dan kontrak modul valid.
- focused WorkLocations dan consumer regression set: 39 test, 213 assertion, seluruhnya lulus.
- php artisan route:list --path=hr/work-locations --json: enam route terdaftar.
- Working tree bersih dan branch dev sinkron dengan origin/dev pada awal audit.

Detail perintah berada di EVIDENCE-MANIFEST.md.

## Baseline Performa

Tidak ada ambang performa numerik yang telah disetujui untuk slice ini. Equivalence dinilai dari tidak adanya perubahan query/algoritma yang disengaja dan tidak adanya regresi yang tampak pada test terfokus. Klaim peningkatan performa tidak dibuat.

## Temuan yang Tidak Mengubah Scope

- Console Dashboard dan HRReports memiliki coupling langsung ke model/tabel WorkLocations yang perlu evaluasi dependency/boundary terpisah.
- permissions.php dan Support/Permissions.php sama-sama mengekspor permission dalam bentuk berbeda.
- Generator saat ini belum menghasilkan struktur target ADR-0001.

Temuan tersebut masuk BACKLOG dan tidak menghalangi refactor struktural karena seluruh consumer akan diperbarui serentak dan perilaku dipertahankan.
