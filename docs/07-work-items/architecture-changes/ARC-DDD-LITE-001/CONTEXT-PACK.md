# Context Pack — ARC-DDD-LITE-001

## Task Berikutnya

Tidak ada task aktif. `TSK-ARC-DDD-LITE-001-03 — Review hasil pilot dan susun dependency order` berstatus `ready`. Pilot child `REF-HR-WLOC-001` telah completed dan context-nya sekarang menjadi bukti historis task tersebut.

## Outcome Task Sebelumnya

Outcome restore tooling tercapai: `app/Support/Modules/Commands/MakeModuleCommand.php` identik dengan commit `f1f64b2661e081ff6f2bf7418ffd9795a0ff11bd`; test generator dan module validation lulus. Outcome pilot berikutnya juga tercapai melalui `REF-HR-WLOC-001`; keduanya menjadi input task review yang ready.

## Fakta Wajib

- Target: ADR-0001, lokasi baku dan folder minimal.
- Perilaku kode harus dipertahankan.
- Current code: 28 manifest dan struktur datar.
- Target catalog: 27 modul setelah deprecation diimplementasikan; arah deprecation sudah accepted dan tidak ada merge.
- `HR/IntegrationContracts` dikelola oleh `DEP-HR-001`, bukan dimigrasikan sebagai modul target.
- ULID dikelola oleh `MIG-ID-001` dan dilarang masuk scope ARC.
- Dokumen historis adalah evidence melalui `BL-2026-001-pre-seos`.
- Runtime module tooling telah dipulihkan dan diverifikasi untuk task restore; adaptasi generator DDD-Lite belum aktif.
- Baseline engineering/testing telah direkonsiliasi melalui `FTR-ENG-001`: web/session aktif, API/token deferred, test module-local incremental, dan angka kualitas belum menjadi gate.
- Keputusan pilot WorkLocations telah disetujui: struktur minimal, route target-first/fallback, namespace tanpa shim, refactor struktural murni, test module-local incremental, dan policy Laravel/Spatie di Presentation.
- Pilot WorkLocations telah membuktikan keputusan tersebut dengan full quality 527 test/3.260 assertion, enam route ekuivalen, authorization allow/deny, dan build frontend yang lulus.

## Area yang Diizinkan

- dokumen task pada `docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/` dan child `REF-HR-WLOC-001`;
- read-only inspection dan command verifikasi repository;
- read-only audit graph import untuk task 03 setelah task tersebut diaktifkan.

## Area yang Dilarang

- perubahan isi/perilaku generator;
- perubahan modul bisnis, test, migration, route, config, dependency, dan generated artifact pada task review;
- `HR/IntegrationContracts` dan work item ULID.

## Risiko

- Menganggap dokumen target sebagai perilaku yang sudah berjalan.
- Menimpa deletion atau perubahan lokal milik user.
- Menyatukan perubahan struktur, rename, deprecation, dan ULID dalam satu task.
- Mengklaim restore atau test berhasil tanpa diff dan output command aktual.
