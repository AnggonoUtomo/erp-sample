---
id: DOC-REF-HR-WLOC-001-CONTEXT
title: Context Pack TSK-REF-HR-WLOC-001-05
document_type: context-pack
status: ready
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, FTR-ENG-001]
---

# Context Pack TSK-REF-HR-WLOC-001-05

## Task yang Dipilih

TSK-REF-HR-WLOC-001-05 — Cutover Model dan Semua Consumer. Status in_progress; ini satu-satunya task coding aktif.

## Work Item Induk

REF-HR-WLOC-001, child CRITICAL dari ARC-DDD-LITE-001 dan implementasi terkontrol untuk TSK-ARC-DDD-LITE-001-02.

## Fakta Repository yang Terverifikasi

- TSK-REF-HR-WLOC-001-01 telah membuat route discovery target-first/fallback dan seluruh pemeriksaannya lulus.
- DTO, transaction, dan service telah berada di lokasi target serta focused test lulus.
- Model WorkLocation memiliki consumer pada WorkLocations, Console Dashboard, Employees, EmployeeMovements, HRReports, seeder, dan enam test.
- Tidak ada alias namespace lama sesuai keputusan manusia.

## Requirement dan Kriteria Penerimaan

- REF-WLOC-REQ-001 sampai REF-WLOC-REQ-006 dan REF-WLOC-NFR-001.
- Model berpindah ke Infrastructure/Models dan seluruh import diperbarui atomik.
- Table, fillable, casts, SoftDeletes, relasi, schema, serta perilaku consumer tetap.

## ADR dan Baseline

- docs/03-architecture/adr/ADR-0001-DDD-Lite-Module-Structure.md
- docs/05-engineering/TESTING-STRATEGY.md
- 01-REFACTORING-PROPOSAL.md
- 02-BEHAVIOR-BASELINE.md
- 03-IMPLEMENTATION-PLAN.md

## File dan Area yang Diizinkan

- file model sumber/target dan seluruh consumer persis pada allowlist lengkap TASKS.md
- dokumen evidence, deviasi, dan task work item ini

## File dan Area yang Dilarang

- seluruh file selain allowlist task 02
- database/** dan seluruh migration
- resources/js/**
- app/Support/Modules/Commands/MakeModuleCommand.php
- permission, policy, authentication, public API, dan integration contract

## Pola yang Harus Dipertahankan

- WorkLocation tetap identik selain namespace.
- Seluruh consumer hanya mengubah import.

## Perintah Verifikasi

    php artisan test tests/Feature/HRWorkLocationTest.php tests/Feature/HREmployeeMovementTest.php tests/Feature/HREmployeeTest.php tests/Feature/HRIntegrationAssignmentSnapshotTest.php tests/Feature/HRReportCommandTest.php tests/Feature/HRReportHeadcountTest.php
    php artisan module:validate HR.WorkLocations --json
    vendor/bin/pint --test app/Modules/HR/WorkLocations app/Http/Controllers/Console/DashboardController.php app/Modules/HR/Employees app/Modules/HR/EmployeeMovements app/Modules/HR/HRReports tests/Feature/HRWorkLocationTest.php tests/Feature/HREmployeeMovementTest.php tests/Feature/HREmployeeTest.php tests/Feature/HRIntegrationAssignmentSnapshotTest.php tests/Feature/HRReportCommandTest.php tests/Feature/HRReportHeadcountTest.php
    git diff --check

## Risiko dan Keputusan

Risiko utama adalah satu consumer terlewat sehingga autoload/test gagal. Mitigasinya cutover atomik, pencarian namespace lama nol hasil, dan regression set 39 test. Tidak ada pertanyaan keputusan blocking.
