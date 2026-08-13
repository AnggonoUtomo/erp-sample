---
id: DOC-REF-HR-WLOC-001-CONTEXT
title: Context Pack TSK-REF-HR-WLOC-001-07
document_type: context-pack
status: ready
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, FTR-ENG-001]
---

# Context Pack TSK-REF-HR-WLOC-001-07

## Task yang Dipilih

TSK-REF-HR-WLOC-001-07 — Pindahkan Policy. Status in_progress; ini satu-satunya task coding aktif.

## Work Item Induk

REF-HR-WLOC-001, child CRITICAL dari ARC-DDD-LITE-001 dan implementasi terkontrol untuk TSK-ARC-DDD-LITE-001-02.

## Fakta Repository yang Terverifikasi

- TSK-REF-HR-WLOC-001-01 telah membuat route discovery target-first/fallback dan seluruh pemeriksaannya lulus.
- DTO, transaction, service, model, consumer, dan FormRequest telah berpindah; focused/regression test lulus.
- WorkLocationPolicy berada di Policies/ dan didaftarkan provider melalui Gate::policy.
- Target Presentation/Policies sesuai ADR-0001 karena policy mengadaptasi Laravel/Spatie dan User.

## Requirement dan Kriteria Penerimaan

- REF-WLOC-REQ-001, REF-WLOC-REQ-004, REF-WLOC-REQ-006, REF-WLOC-NFR-001, dan REF-WLOC-NFR-004.
- Policy berpindah dan import provider diperbarui.
- Permission check, Gate mapping, allow, dan deny tetap.

## ADR dan Baseline

- docs/03-architecture/adr/ADR-0001-DDD-Lite-Module-Structure.md
- docs/05-engineering/TESTING-STRATEGY.md
- 01-REFACTORING-PROPOSAL.md
- 02-BEHAVIOR-BASELINE.md
- 03-IMPLEMENTATION-PLAN.md

## File dan Area yang Diizinkan

- WorkLocationPolicy sumber/target, WorkLocationsServiceProvider, dan HRWorkLocationTest
- dokumen evidence, deviasi, dan task work item ini

## File dan Area yang Dilarang

- seluruh file selain allowlist task 02
- database/** dan seluruh migration
- resources/js/**
- app/Support/Modules/Commands/MakeModuleCommand.php
- permission, policy, authentication, public API, dan integration contract

## Pola yang Harus Dipertahankan

- Policy tetap identik selain namespace.
- Provider hanya mengubah import.
- Feature test membuktikan allow/deny; Gate mapping ditambahkan sebagai characterization assertion.

## Perintah Verifikasi

    php artisan test tests/Feature/HRWorkLocationTest.php
    php artisan module:validate HR.WorkLocations --json
    vendor/bin/pint --test app/Modules/HR/WorkLocations/Presentation/Policies/WorkLocationPolicy.php app/Modules/HR/WorkLocations/Providers/WorkLocationsServiceProvider.php tests/Feature/HRWorkLocationTest.php
    git diff --check

## Risiko dan Keputusan

Risiko utama adalah Gate masih menunjuk class lama atau permission semantics berubah. Mitigasinya diff exact, Gate mapping assertion, allow/deny HTTP test, dan pencarian namespace lama. Tidak ada pertanyaan keputusan blocking.
