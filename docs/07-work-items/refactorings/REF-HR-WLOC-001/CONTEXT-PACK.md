---
id: DOC-REF-HR-WLOC-001-CONTEXT
title: Context Pack TSK-REF-HR-WLOC-001-03
document_type: context-pack
status: ready
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, FTR-ENG-001]
---

# Context Pack TSK-REF-HR-WLOC-001-03

## Task yang Dipilih

TSK-REF-HR-WLOC-001-03 — Pindahkan Transaction. Status in_progress; ini satu-satunya task coding aktif.

## Work Item Induk

REF-HR-WLOC-001, child CRITICAL dari ARC-DDD-LITE-001 dan implementasi terkontrol untuk TSK-ARC-DDD-LITE-001-02.

## Fakta Repository yang Terverifikasi

- TSK-REF-HR-WLOC-001-01 telah membuat route discovery target-first/fallback dan seluruh pemeriksaannya lulus.
- WorkLocationData telah berada di Application/DTOs dan focused test lulus.
- WorkLocationsTransaction hanya membungkus DB::transaction dan dipakai WorkLocationsService.
- Target Infrastructure/Transactions menandai wrapper tersebut sebagai detail teknis Laravel.

## Requirement dan Kriteria Penerimaan

- REF-WLOC-REQ-002, REF-WLOC-REQ-004, REF-WLOC-REQ-006, dan REF-WLOC-NFR-003.
- File/namespace transaction berpindah dan import service diperbarui.
- Method run dan DB::transaction tetap identik.

## ADR dan Baseline

- docs/03-architecture/adr/ADR-0001-DDD-Lite-Module-Structure.md
- docs/05-engineering/TESTING-STRATEGY.md
- 01-REFACTORING-PROPOSAL.md
- 02-BEHAVIOR-BASELINE.md
- 03-IMPLEMENTATION-PLAN.md

## File dan Area yang Diizinkan

- app/Modules/HR/WorkLocations/Transactions/WorkLocationsTransaction.php
- app/Modules/HR/WorkLocations/Infrastructure/Transactions/WorkLocationsTransaction.php
- app/Modules/HR/WorkLocations/Services/WorkLocationsService.php
- dokumen evidence, deviasi, dan task work item ini

## File dan Area yang Dilarang

- seluruh file selain allowlist task 02
- database/** dan seluruh migration
- resources/js/**
- app/Support/Modules/Commands/MakeModuleCommand.php
- permission, policy, authentication, public API, dan integration contract

## Pola yang Harus Dipertahankan

- WorkLocationsTransaction tetap wrapper tipis final behavior yang sama.
- Consumer hanya mengubah import.

## Perintah Verifikasi

    php artisan test tests/Feature/HRWorkLocationTest.php
    php artisan module:validate HR.WorkLocations --json
    vendor/bin/pint --test app/Modules/HR/WorkLocations/Infrastructure/Transactions/WorkLocationsTransaction.php app/Modules/HR/WorkLocations/Services/WorkLocationsService.php
    git diff --check

## Risiko dan Keputusan

Risiko utama adalah service gagal me-resolve dependency transaction. Mitigasinya pencarian namespace lama dan HRWorkLocationTest. Tidak ada pertanyaan keputusan blocking.
