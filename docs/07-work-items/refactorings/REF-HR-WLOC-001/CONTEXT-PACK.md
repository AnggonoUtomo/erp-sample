---
id: DOC-REF-HR-WLOC-001-CONTEXT
title: Context Pack TSK-REF-HR-WLOC-001-08
document_type: context-pack
status: ready
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, FTR-ENG-001]
---

# Context Pack TSK-REF-HR-WLOC-001-08

## Task yang Dipilih

TSK-REF-HR-WLOC-001-08 — Pindahkan Controller. Status in_progress; ini satu-satunya task coding aktif.

## Work Item Induk

REF-HR-WLOC-001, child CRITICAL dari ARC-DDD-LITE-001 dan implementasi terkontrol untuk TSK-ARC-DDD-LITE-001-02.

## Fakta Repository yang Terverifikasi

- TSK-REF-HR-WLOC-001-01 telah membuat route discovery target-first/fallback dan seluruh pemeriksaannya lulus.
- Seluruh concern sebelum controller telah berpindah dan focused/regression test lulus.
- Controller berada di Http/Controllers dan dipakai root routes.php.
- Target Presentation/Http/Controllers tidak mengubah method, middleware, response, atau redirect.

## Requirement dan Kriteria Penerimaan

- REF-WLOC-REQ-001, REF-WLOC-REQ-002, REF-WLOC-REQ-004, dan REF-WLOC-REQ-006.
- Controller berpindah dan import route diperbarui.
- Enam action, middleware, Inertia page, redirects, dan messages tetap.

## ADR dan Baseline

- docs/03-architecture/adr/ADR-0001-DDD-Lite-Module-Structure.md
- docs/05-engineering/TESTING-STRATEGY.md
- 01-REFACTORING-PROPOSAL.md
- 02-BEHAVIOR-BASELINE.md
- 03-IMPLEMENTATION-PLAN.md

## File dan Area yang Diizinkan

- WorkLocationsController sumber/target dan routes.php
- dokumen evidence, deviasi, dan task work item ini

## File dan Area yang Dilarang

- seluruh file selain allowlist task 02
- database/** dan seluruh migration
- resources/js/**
- app/Support/Modules/Commands/MakeModuleCommand.php
- permission, policy, authentication, public API, dan integration contract

## Pola yang Harus Dipertahankan

- Controller tetap identik selain namespace.
- Route hanya mengubah import.

## Perintah Verifikasi

    php artisan test tests/Feature/HRWorkLocationTest.php
    php artisan module:validate HR.WorkLocations --json
    vendor/bin/pint --test app/Modules/HR/WorkLocations/Presentation/Http/Controllers/WorkLocationsController.php app/Modules/HR/WorkLocations/routes.php
    git diff --check

## Risiko dan Keputusan

Risiko utama adalah route action atau middleware mengarah ke class lama. Mitigasinya namespace search, focused test, dan snapshot route. Tidak ada pertanyaan keputusan blocking.
