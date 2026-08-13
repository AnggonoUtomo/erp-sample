---
id: DOC-REF-HR-WLOC-001-TASKS
title: Task REF-HR-WLOC-001
document_type: work-item-tasks
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001]
---

# Task REF-HR-WLOC-001

## Urutan

| Urutan | Task ID | Tujuan | Bergantung pada | Status |
|---:|---|---|---|---|
| 0 | TSK-REF-HR-WLOC-001-00 | Pra-kerja, baseline, approval, readiness | FTR-ENG-001 | completed |
| 1 | TSK-REF-HR-WLOC-001-01 | Route discovery target-first/fallback | task 00 | completed |
| 2 | TSK-REF-HR-WLOC-001-02 | Pindahkan DTO | task 01 | completed |
| 3 | TSK-REF-HR-WLOC-001-03 | Pindahkan transaction | task 02 | completed |
| 4 | TSK-REF-HR-WLOC-001-04 | Pindahkan service | task 03 | in_progress |
| 5 | TSK-REF-HR-WLOC-001-05 | Cutover model dan semua consumer | task 04 | pending |
| 6 | TSK-REF-HR-WLOC-001-06 | Pindahkan request | task 05 | pending |
| 7 | TSK-REF-HR-WLOC-001-07 | Pindahkan policy | task 06 | pending |
| 8 | TSK-REF-HR-WLOC-001-08 | Pindahkan controller | task 07 | pending |
| 9 | TSK-REF-HR-WLOC-001-09 | Pindahkan provider | task 08 | pending |
| 10 | TSK-REF-HR-WLOC-001-10 | Pindahkan route | task 09 | pending |
| 11 | TSK-REF-HR-WLOC-001-11 | Colocation test modul dan discovery PHPUnit | task 10 | pending |
| 12 | TSK-REF-HR-WLOC-001-12 | Verifikasi, review, dan baseline sync | task 11 | pending |

Task 04 adalah satu-satunya task coding berstatus in_progress.

## TSK-REF-HR-WLOC-001-01 — Route Discovery Target-First/Fallback

Status: completed. Owner: Codex. Ukuran: small.

Tujuan: membuat ModuleRegistry dan ModuleContractValidator menerima Presentation/Routes/web.php sebagai target utama dan root routes.php sebagai fallback umum.

Area diizinkan:

- app/Support/Modules/ModuleRegistry.php
- app/Support/Modules/ModuleContractValidator.php
- tests/Unit/ModuleContractValidatorTest.php
- tests/Unit/ModuleRegistryTest.php sebagai test baru bila diperlukan

Area dilarang: file modul bisnis, generator, schema, authentication/authorization, route behavior.

Kriteria penerimaan: target menang bila target/fallback sama-sama ada; fallback tetap bekerja bagi modul lama; route tidak didaftarkan dua kali; module validation dan unit test lulus.

Verifikasi:

    php artisan test tests/Unit/ModuleContractValidatorTest.php tests/Unit/ModuleRegistryTest.php
    php artisan module:validate
    php artisan route:list --path=hr/work-locations --json
    git diff --check

Hasil: 5 test/6 assertion lulus; seluruh module contract valid; enam route baseline tidak berubah; Pint dan diff check lulus.

## TSK-REF-HR-WLOC-001-02 — Pindahkan DTO

Pindahkan DTO/WorkLocationData.php ke Application/DTOs/ dan ubah import pada dua request serta service. Verifikasi syntax dan HRWorkLocationTest. Dilarang mengubah field, default, dan transformasi DTO.

## TSK-REF-HR-WLOC-001-03 — Pindahkan Transaction

Pindahkan Transactions/WorkLocationsTransaction.php ke Infrastructure/Transactions/ dan ubah import service. Dilarang mengubah semantics transaksi. Verifikasi syntax dan HRWorkLocationTest.

## TSK-REF-HR-WLOC-001-04 — Pindahkan Service

Pindahkan Services/WorkLocationsService.php ke Application/Services/ dan ubah import controller. Dilarang mengubah query, audit, map settings, pagination, atau operasi CRUD. Verifikasi HRWorkLocationTest.

## TSK-REF-HR-WLOC-001-05 — Cutover Model dan Semua Consumer

Pindahkan Models/WorkLocation.php ke Infrastructure/Models/ dan perbarui seluruh import pada WorkLocations, Console Dashboard, Employees, EmployeeMovements, HRReports, seeder, serta enam test yang terinventarisasi. Task ini sengaja lebih besar karena keputusan tanpa alias mengharuskan cutover atomik.

Kriteria penerimaan: tidak ada import App\Modules\HR\WorkLocations\Models\WorkLocation tersisa pada app/, database/, atau tests/; schema/model behavior tetap; focused dan consumer regression set lulus.

## TSK-REF-HR-WLOC-001-06 — Pindahkan Request

Pindahkan dua FormRequest ke Presentation/Http/Requests/ dan ubah import controller. Dilarang mengubah authorization, validation, atau normalization. Verifikasi HRWorkLocationTest.

## TSK-REF-HR-WLOC-001-07 — Pindahkan Policy

Pindahkan WorkLocationPolicy ke Presentation/Policies/ dan ubah import provider. Policy tetap adapter Laravel/Spatie; tidak dipindahkan ke Domain/. Permission semantics tidak berubah.

## TSK-REF-HR-WLOC-001-08 — Pindahkan Controller

Pindahkan controller ke Presentation/Http/Controllers/ dan ubah import route aktif. Dilarang mengubah middleware, response, redirect, dan message.

## TSK-REF-HR-WLOC-001-09 — Pindahkan Provider

Pindahkan provider ke Infrastructure/Providers/ dan ubah module.php. Gate policy dan registration behavior tetap.

## TSK-REF-HR-WLOC-001-10 — Pindahkan Route

Pindahkan root routes.php ke Presentation/Routes/web.php setelah discovery target terbukti. Root fallback tidak boleh tersisa pada WorkLocations dan enam route harus tetap persis satu kali terdaftar.

## TSK-REF-HR-WLOC-001-11 — Colocation Test dan Discovery PHPUnit

Pindahkan tests/Feature/HRWorkLocationTest.php ke app/Modules/HR/WorkLocations/Tests/Feature/, sesuaikan namespace test agar konsisten dengan autoload App\, dan tambahkan discovery PHPUnit secara additive. Test lintas sistem tetap pada tests/Feature.

Pertahankan seluruh assertion yang ada dan tambahkan characterization assertion bahwa pengguna tanpa permission menerima HTTP 403 ketika membuka index; penambahan test tidak mengubah perilaku aplikasi.

Area diizinkan adalah file test lama dan baru serta phpunit.xml.

## TSK-REF-HR-WLOC-001-12 — Verifikasi dan Baseline Sync

Jalankan seluruh perintah 04-BEHAVIOR-VALIDATION.md, lakukan review multi-axis, isi evidence/deviasi/completion, sinkronkan registry, induk ARC-DDD-LITE-001, implementation plan, module catalog bila status struktur berubah, dan file inventory. Dilarang memperbaiki temuan di luar scope tanpa klasifikasi.

## Allowlist Implementasi Lengkap

Selain file tooling dan test pada task 01, implementasi boleh menyentuh file berikut sesuai task-nya:

- app/Modules/HR/WorkLocations/DTO/WorkLocationData.php dan target Application/DTOs/WorkLocationData.php
- app/Modules/HR/WorkLocations/Services/WorkLocationsService.php dan target Application/Services/WorkLocationsService.php
- app/Modules/HR/WorkLocations/Models/WorkLocation.php dan target Infrastructure/Models/WorkLocation.php
- app/Modules/HR/WorkLocations/Providers/WorkLocationsServiceProvider.php dan target Infrastructure/Providers/WorkLocationsServiceProvider.php
- app/Modules/HR/WorkLocations/Transactions/WorkLocationsTransaction.php dan target Infrastructure/Transactions/WorkLocationsTransaction.php
- app/Modules/HR/WorkLocations/Http/Controllers/WorkLocationsController.php dan target Presentation/Http/Controllers/WorkLocationsController.php
- app/Modules/HR/WorkLocations/Http/Requests/StoreWorkLocationRequest.php serta UpdateWorkLocationRequest.php dan target Presentation/Http/Requests/
- app/Modules/HR/WorkLocations/Policies/WorkLocationPolicy.php dan target Presentation/Policies/WorkLocationPolicy.php
- app/Modules/HR/WorkLocations/routes.php dan target Presentation/Routes/web.php
- app/Modules/HR/WorkLocations/module.php
- app/Modules/HR/WorkLocations/Database/Seeders/HRWorkLocationSeeder.php
- app/Http/Controllers/Console/DashboardController.php
- app/Modules/HR/EmployeeMovements/Services/EmployeeMovementsService.php
- app/Modules/HR/Employees/Database/Seeders/HREmployeeSeeder.php
- app/Modules/HR/Employees/Models/Employee.php
- app/Modules/HR/Employees/Services/EmployeesService.php
- app/Modules/HR/HRReports/Database/Seeders/HRReportLifecycleSeeder.php
- tests/Feature/HRWorkLocationTest.php dan target app/Modules/HR/WorkLocations/Tests/Feature/HRWorkLocationTest.php
- tests/Feature/HREmployeeMovementTest.php
- tests/Feature/HREmployeeTest.php
- tests/Feature/HRIntegrationAssignmentSnapshotTest.php
- tests/Feature/HRReportCommandTest.php
- tests/Feature/HRReportHeadcountTest.php
- phpunit.xml
- dokumentasi work item, induk, registry, planning, katalog, baseline, dan inventaris yang diwajibkan sync matrix.

File migration, permissions.php, navigation.php, Support/Permissions.php, seluruh resources/js/, HR/IntegrationContracts, composer dependency, dan generator berada di luar allowlist. File consumer baru hanya boleh ditambahkan melalui catatan deviasi dan pembaruan scope sebelum disentuh.
