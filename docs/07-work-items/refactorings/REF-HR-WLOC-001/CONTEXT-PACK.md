---
id: DOC-REF-HR-WLOC-001-CONTEXT
title: Context Pack TSK-REF-HR-WLOC-001-11
document_type: context-pack
status: ready
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, FTR-ENG-001]
---

# Context Pack TSK-REF-HR-WLOC-001-11

## Task yang Dipilih

TSK-REF-HR-WLOC-001-11 — Colocation Test dan Discovery PHPUnit. Status in_progress; ini satu-satunya task coding aktif.

## Work Item Induk

REF-HR-WLOC-001, child CRITICAL dari ARC-DDD-LITE-001 dan implementasi terkontrol untuk TSK-ARC-DDD-LITE-001-02.

## Fakta Repository yang Terverifikasi

- Seluruh backend dan route WorkLocations telah berada di lokasi target; focused/regression test lulus.
- HRWorkLocationTest masih berada di tests/Feature dengan namespace Tests/Feature.
- Target ADR-0001 memindahkan hanya test milik modul; test lintas sistem tetap di tests/Feature.

## Requirement dan Kriteria Penerimaan

- REF-WLOC-REQ-001, REF-WLOC-REQ-007, REF-WLOC-REQ-008, dan REF-WLOC-NFR-003.
- HRWorkLocationTest berpindah ke Tests/Feature di dalam modul dengan namespace App yang sesuai path.
- phpunit.xml menemukan test module-local secara additive; test lintas sistem tetap.
- Characterization test denial 403 pada index ditambahkan tanpa mengurangi assertion lama.

## ADR dan Baseline

- docs/03-architecture/adr/ADR-0001-DDD-Lite-Module-Structure.md
- docs/05-engineering/TESTING-STRATEGY.md
- 01-REFACTORING-PROPOSAL.md
- 02-BEHAVIOR-BASELINE.md
- 03-IMPLEMENTATION-PLAN.md

## File dan Area yang Diizinkan

- tests/Feature/HRWorkLocationTest.php
- app/Modules/HR/WorkLocations/Tests/Feature/HRWorkLocationTest.php
- phpunit.xml
- dokumen evidence, deviasi, dan task work item ini

## File dan Area yang Dilarang

- test lintas sistem lain
- database/** dan seluruh migration
- resources/js/**
- generator, permission, policy, authentication, public API, dan integration contract

## Pola yang Harus Dipertahankan

- Test memakai Tests/TestCase dan RefreshDatabase seperti baseline.
- Namespace test target mengikuti App/Modules path; behavior assertion tetap.
- PHPUnit menambah suite Module tanpa mengganti Unit/Feature.

## Perintah Verifikasi

    php artisan test app/Modules/HR/WorkLocations/Tests/Feature/HRWorkLocationTest.php
    php artisan test --testsuite=Module
    composer dump-autoload --strict-psr
    vendor/bin/pint --test app/Modules/HR/WorkLocations/Tests/Feature/HRWorkLocationTest.php
    git diff --check

## Risiko dan Keputusan

Risiko utama adalah PHPUnit tidak menemukan test target atau production autoload tidak konsisten. Mitigasinya path eksplisit, suite Module, Composer strict PSR, dan full suite pada task 12. Tidak ada pertanyaan keputusan blocking.
