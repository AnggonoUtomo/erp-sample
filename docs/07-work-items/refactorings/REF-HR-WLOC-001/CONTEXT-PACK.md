---
id: DOC-REF-HR-WLOC-001-CONTEXT
title: Context Pack TSK-REF-HR-WLOC-001-09
document_type: context-pack
status: ready
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, FTR-ENG-001]
---

# Context Pack TSK-REF-HR-WLOC-001-09

## Task yang Dipilih

TSK-REF-HR-WLOC-001-09 — Pindahkan Provider. Status in_progress; ini satu-satunya task coding aktif.

## Work Item Induk

REF-HR-WLOC-001, child CRITICAL dari ARC-DDD-LITE-001 dan implementasi terkontrol untuk TSK-ARC-DDD-LITE-001-02.

## Fakta Repository yang Terverifikasi

- TSK-REF-HR-WLOC-001-01 telah membuat route discovery target-first/fallback dan seluruh pemeriksaannya lulus.
- Controller dan concern sebelumnya telah berpindah; focused/regression test lulus.
- WorkLocationsServiceProvider berada di Providers/ dan direferensikan module.php.
- Target Infrastructure/Providers mempertahankan Gate::policy.

## Requirement dan Kriteria Penerimaan

- REF-WLOC-REQ-001, REF-WLOC-REQ-004, REF-WLOC-REQ-006, dan REF-WLOC-NFR-004.
- Provider berpindah dan import module.php diperbarui.
- Gate mapping tetap menunjuk model/policy target.

## ADR dan Baseline

- docs/03-architecture/adr/ADR-0001-DDD-Lite-Module-Structure.md
- docs/05-engineering/TESTING-STRATEGY.md
- 01-REFACTORING-PROPOSAL.md
- 02-BEHAVIOR-BASELINE.md
- 03-IMPLEMENTATION-PLAN.md

## File dan Area yang Diizinkan

- WorkLocationsServiceProvider sumber/target, module.php, dan HRWorkLocationTest
- dokumen evidence, deviasi, dan task work item ini

## File dan Area yang Dilarang

- seluruh file selain allowlist task 02
- database/** dan seluruh migration
- resources/js/**
- app/Support/Modules/Commands/MakeModuleCommand.php
- permission, policy, authentication, public API, dan integration contract

## Pola yang Harus Dipertahankan

- Provider tetap identik selain namespace.
- module.php hanya mengubah import provider.

## Perintah Verifikasi

    php artisan test tests/Feature/HRWorkLocationTest.php
    php artisan module:validate HR.WorkLocations --json
    vendor/bin/pint --test app/Modules/HR/WorkLocations/Infrastructure/Providers/WorkLocationsServiceProvider.php app/Modules/HR/WorkLocations/module.php tests/Feature/HRWorkLocationTest.php
    git diff --check

## Risiko dan Keputusan

Risiko utama adalah manifest membuang provider yang tidak dapat di-resolve atau Gate mapping hilang. Mitigasinya module validation, Gate assertion, allow/deny test, dan namespace search. Tidak ada pertanyaan keputusan blocking.
