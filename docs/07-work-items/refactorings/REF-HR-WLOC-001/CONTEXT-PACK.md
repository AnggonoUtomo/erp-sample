---
id: DOC-REF-HR-WLOC-001-CONTEXT
title: Context Pack TSK-REF-HR-WLOC-001-10
document_type: context-pack
status: ready
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, FTR-ENG-001]
---

# Context Pack TSK-REF-HR-WLOC-001-10

## Task yang Dipilih

TSK-REF-HR-WLOC-001-10 — Pindahkan Route. Status in_progress; ini satu-satunya task coding aktif.

## Work Item Induk

REF-HR-WLOC-001, child CRITICAL dari ARC-DDD-LITE-001 dan implementasi terkontrol untuk TSK-ARC-DDD-LITE-001-02.

## Fakta Repository yang Terverifikasi

- TSK-REF-HR-WLOC-001-01 telah membuat route discovery target-first/fallback dan seluruh pemeriksaannya lulus.
- Provider dan seluruh class backend pilot telah berpindah; focused/regression test lulus.
- Root routes.php masih dipakai melalui fallback yang sudah terbukti.
- Target Presentation/Routes/web.php harus menang tanpa double registration.

## Requirement dan Kriteria Penerimaan

- REF-WLOC-REQ-001, REF-WLOC-REQ-004, REF-WLOC-REQ-005, dan REF-WLOC-REQ-006.
- routes.php berpindah ke Presentation/Routes/web.php.
- Enam route dan seluruh semantics tetap; root routes.php hilang.

## ADR dan Baseline

- docs/03-architecture/adr/ADR-0001-DDD-Lite-Module-Structure.md
- docs/05-engineering/TESTING-STRATEGY.md
- 01-REFACTORING-PROPOSAL.md
- 02-BEHAVIOR-BASELINE.md
- 03-IMPLEMENTATION-PLAN.md

## File dan Area yang Diizinkan

- routes.php, Presentation/Routes/web.php, route/module verification, dan HRWorkLocationTest
- dokumen evidence, deviasi, dan task work item ini

## File dan Area yang Dilarang

- seluruh file selain allowlist task 02
- database/** dan seluruh migration
- resources/js/**
- app/Support/Modules/Commands/MakeModuleCommand.php
- permission, policy, authentication, public API, dan integration contract

## Pola yang Harus Dipertahankan

- Isi route tetap identik.
- ModuleRegistry memilih target dan tidak memuat fallback bila target ada.

## Perintah Verifikasi

    php artisan test tests/Feature/HRWorkLocationTest.php
    php artisan module:validate HR.WorkLocations --json
    vendor/bin/pint --test app/Modules/HR/WorkLocations/Presentation/Routes/web.php
    git diff --check

## Risiko dan Keputusan

Risiko utama adalah route hilang atau terdaftar ganda. Mitigasinya focused test, module validation, snapshot tepat enam route, dan verifikasi root routes.php tidak ada. Tidak ada pertanyaan keputusan blocking.
