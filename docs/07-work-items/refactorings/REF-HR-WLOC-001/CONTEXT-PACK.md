---
id: DOC-REF-HR-WLOC-001-CONTEXT
title: Context Pack TSK-REF-HR-WLOC-001-02
document_type: context-pack
status: ready
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, FTR-ENG-001]
---

# Context Pack TSK-REF-HR-WLOC-001-02

## Task yang Dipilih

TSK-REF-HR-WLOC-001-02 — Pindahkan DTO. Status in_progress; ini satu-satunya task coding aktif.

## Work Item Induk

REF-HR-WLOC-001, child CRITICAL dari ARC-DDD-LITE-001 dan implementasi terkontrol untuk TSK-ARC-DDD-LITE-001-02.

## Fakta Repository yang Terverifikasi

- TSK-REF-HR-WLOC-001-01 telah membuat route discovery target-first/fallback dan seluruh pemeriksaannya lulus.
- WorkLocationData saat ini berada di DTO/ dan dipakai oleh dua FormRequest serta WorkLocationsService.
- ADR-0001 menetapkan DTO di Application/DTOs/.
- Field, default, fromArray, dan normalization DTO harus tetap persis.

## Requirement dan Kriteria Penerimaan

- REF-WLOC-REQ-002, REF-WLOC-REQ-004, REF-WLOC-REQ-006, dan REF-WLOC-NFR-001.
- File/namespace berpindah ke Application/DTOs/ dan tiga consumer langsung diperbarui.
- Tidak ada perubahan field, default, transformasi, validation, atau behavior HTTP.

## ADR dan Baseline

- docs/03-architecture/adr/ADR-0001-DDD-Lite-Module-Structure.md
- docs/05-engineering/TESTING-STRATEGY.md
- 01-REFACTORING-PROPOSAL.md
- 02-BEHAVIOR-BASELINE.md
- 03-IMPLEMENTATION-PLAN.md

## File dan Area yang Diizinkan

- app/Modules/HR/WorkLocations/DTO/WorkLocationData.php
- app/Modules/HR/WorkLocations/Application/DTOs/WorkLocationData.php
- app/Modules/HR/WorkLocations/Http/Requests/StoreWorkLocationRequest.php
- app/Modules/HR/WorkLocations/Http/Requests/UpdateWorkLocationRequest.php
- app/Modules/HR/WorkLocations/Services/WorkLocationsService.php
- dokumen evidence, deviasi, dan task work item ini

## File dan Area yang Dilarang

- seluruh file selain allowlist task 02
- database/** dan seluruh migration
- resources/js/**
- app/Support/Modules/Commands/MakeModuleCommand.php
- permission, policy, authentication, public API, dan integration contract

## Pola yang Harus Dipertahankan

- WorkLocationData tetap final readonly.
- Constructor dan fromArray tetap identik selain namespace.
- Consumer hanya mengubah import.

## Perintah Verifikasi

    php artisan test tests/Feature/HRWorkLocationTest.php
    php artisan module:validate HR.WorkLocations --json
    vendor/bin/pint --test app/Modules/HR/WorkLocations/Application/DTOs/WorkLocationData.php app/Modules/HR/WorkLocations/Http/Requests app/Modules/HR/WorkLocations/Services/WorkLocationsService.php
    git diff --check

## Risiko dan Keputusan

Risiko utama adalah import lama terlewat atau perubahan mekanis ikut mengubah DTO. Mitigasinya pencarian namespace lama, diff exact selain namespace, dan HRWorkLocationTest. Tidak ada pertanyaan keputusan blocking.
