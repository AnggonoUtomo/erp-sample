---
id: DOC-REF-HR-WLOC-001-CONTEXT
title: Context Pack TSK-REF-HR-WLOC-001-01
document_type: context-pack
status: ready
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-14
updated: 2026-08-14
source_work_item: REF-HR-WLOC-001
related: [ADR-0001, FTR-ENG-001]
---

# Context Pack TSK-REF-HR-WLOC-001-01

## Task yang Dipilih

TSK-REF-HR-WLOC-001-01 — Route Discovery Target-First/Fallback. Status ready; belum in_progress dan belum ada coding.

## Work Item Induk

REF-HR-WLOC-001, child CRITICAL dari ARC-DDD-LITE-001 dan implementasi terkontrol untuk TSK-ARC-DDD-LITE-001-02.

## Fakta Repository yang Terverifikasi

- ModuleRegistry saat ini menemukan root routes.php.
- ModuleContractValidator saat ini mengharuskan root routes.php.
- Seluruh modul aktif masih menggunakan root routes.php pada baseline audit.
- HR/WorkLocations mendaftarkan enam route web/session dan validasi modul lulus.
- ADR-0001 menetapkan route modul di Presentation/Routes/.
- Generator tidak diubah pada task ini.

## Requirement dan Kriteria Penerimaan

- REF-WLOC-REQ-005 dan REF-WLOC-NFR-001.
- Resolver bersifat umum, target-first, fallback root, dan tidak double-load.
- Existing modules tetap valid sebelum dimigrasikan.
- Snapshot enam route WorkLocations tidak berubah.

## ADR dan Baseline

- docs/03-architecture/adr/ADR-0001-DDD-Lite-Module-Structure.md
- docs/05-engineering/TESTING-STRATEGY.md
- 01-REFACTORING-PROPOSAL.md
- 02-BEHAVIOR-BASELINE.md
- 03-IMPLEMENTATION-PLAN.md

## File dan Area yang Diizinkan

- app/Support/Modules/ModuleRegistry.php
- app/Support/Modules/ModuleContractValidator.php
- tests/Unit/ModuleContractValidatorTest.php
- tests/Unit/ModuleRegistryTest.php bila test baru diperlukan
- dokumen evidence, deviasi, dan task work item ini

## File dan Area yang Dilarang

- app/Modules/** pada task 01
- database/** dan seluruh migration
- resources/js/**
- app/Support/Modules/Commands/MakeModuleCommand.php
- permission, policy, authentication, public API, dan integration contract

## Pola yang Harus Dipertahankan

- Path dibentuk dari basePath modul yang sudah ditemukan registry.
- Urutan modul dan exception behavior tetap.
- Target/fallback menghasilkan paling banyak satu route file per modul.
- Validator dan runtime memakai aturan resolusi yang sama.

## Perintah Verifikasi

    php artisan test tests/Unit/ModuleContractValidatorTest.php tests/Unit/ModuleRegistryTest.php
    php artisan module:validate
    php artisan route:list --path=hr/work-locations --json
    git diff --check

## Risiko dan Keputusan

Risiko utama adalah validator dan runtime berbeda atau route dimuat ganda. Mitigasinya satu aturan target-first yang dibuktikan untuk tiga kondisi: hanya legacy, hanya target, dan keduanya ada. Tidak ada pertanyaan keputusan blocking.
