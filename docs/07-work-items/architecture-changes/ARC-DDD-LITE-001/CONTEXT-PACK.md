# Context Pack — ARC-DDD-LITE-001

## Task Aktif

Tidak ada task coding aktif. `TSK-ARC-DDD-LITE-001-01 — Pulihkan konsistensi module tooling` telah completed dan terverifikasi.

## Outcome Task

Outcome tercapai: `app/Support/Modules/Commands/MakeModuleCommand.php` identik dengan commit `f1f64b2661e081ff6f2bf7418ffd9795a0ff11bd`; test generator dan module validation lulus.

## Fakta Wajib

- Target: ADR-0001, lokasi baku dan folder minimal.
- Perilaku kode harus dipertahankan.
- Current code: 28 manifest dan struktur datar.
- Target catalog: 27 modul setelah deprecation diimplementasikan; arah deprecation sudah accepted dan tidak ada merge.
- `HR/IntegrationContracts` dikelola oleh `DEP-HR-001`, bukan dimigrasikan sebagai modul target.
- ULID dikelola oleh `MIG-ID-001` dan dilarang masuk scope ARC.
- Dokumen historis adalah evidence melalui `BL-2026-001-pre-seos`.
- Runtime module tooling saat ini terblokir oleh class command yang hilang.

## Area yang Diizinkan

- `app/Support/Modules/Commands/MakeModuleCommand.php` hanya untuk restore exact dari `HEAD`;
- dokumen task pada `docs/07-work-items/architecture-changes/ARC-DDD-LITE-001/`;
- read-only inspection dan command verifikasi repository.

## Area yang Dilarang

- perubahan isi/perilaku generator;
- seluruh modul bisnis, test, migration, route, config, dependency, dan generated artifact;
- `HR/IntegrationContracts` dan work item ULID.

## Risiko

- Menganggap dokumen target sebagai perilaku yang sudah berjalan.
- Menimpa deletion atau perubahan lokal milik user.
- Menyatukan perubahan struktur, rename, deprecation, dan ULID dalam satu task.
- Mengklaim restore atau test berhasil tanpa diff dan output command aktual.
