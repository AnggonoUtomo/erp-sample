# Work Item Registry

| ID | Jenis | Klasifikasi | Nama | Status | Owner | Induk | Path | Target rilis |
|---|---|---|---|---|---|---|---|---|
| `ARC-DDD-LITE-001` | architecture-change | CRITICAL | Restrukturisasi Modul ke DDD-Lite Adaptif | in_progress | unassigned | null | `docs/07-work-items/architecture-changes/ARC-DDD-LITE-001` | belum ditetapkan |
| `DEP-HR-001` | deprecation-removal | CRITICAL | Deprecation Modul Teknis HR IntegrationContracts | approved | unassigned | `ARC-DDD-LITE-001` | `docs/07-work-items/deprecations/DEP-HR-001` | belum ditetapkan |
| `MIG-ID-001` | data-migration | CRITICAL | Evaluasi dan Migrasi Identifier ke ULID | deferred | unassigned | null | `docs/07-work-items/data-migrations/MIG-ID-001` | belum ditetapkan |
| `ARC-SEOS-WORK-001` | architecture-change | SIGNIFICANT | Standardisasi Paket Dokumentasi per Pekerjaan | completed | Pemilik proyek | null | `docs/07-work-items/architecture-changes/ARC-SEOS-WORK-001` | dokumentasi |
| `PHASE-01-FOUNDATION-CONSOLE-CORE` | feature | CRITICAL | Paket Perencanaan Foundation dan Console Core Lama | superseded | unassigned | `ARC-DDD-LITE-001` | `docs/07-work-items/features/PHASE-01-FOUNDATION-CONSOLE-CORE` | tidak berlaku |
| `FTR-PROD-001` | feature | SIGNIFICANT | Rekonsiliasi Baseline Produk dan Requirement | completed | Pemilik proyek | null | `docs/07-work-items/features/FTR-PROD-001-product-baseline-reconciliation` | dokumentasi |
| `FTR-ENG-001` | feature | SIGNIFICANT | Rekonsiliasi Baseline Engineering dan Testing | completed | Pemilik proyek | null | `docs/07-work-items/features/FTR-ENG-001-engineering-baseline-reconciliation` | dokumentasi |
| `REF-HR-WLOC-001` | refactoring | CRITICAL | Pilot DDD-Lite HR WorkLocations | ready | Pemilik proyek | `ARC-DDD-LITE-001` | `docs/07-work-items/refactorings/REF-HR-WLOC-001` | belum ditetapkan |

## Task Implementasi yang Dipilih

`TSK-ARC-DDD-LITE-001-01 — Pulihkan konsistensi module tooling` telah `completed` dengan restore exact dan verifikasi terfokus. Tidak ada task coding aktif berikutnya.

`TSK-ARC-SEOS-WORK-001-05` telah selesai sebagai pekerjaan dokumentasi. Tidak ada task aplikasi atau coding yang aktif.

Seluruh task `FTR-PROD-001` telah selesai. Tidak ada task aplikasi, coding, atau dokumentasi yang aktif.

Seluruh task `FTR-ENG-001` telah selesai. Tidak ada task engineering atau dokumentasi yang aktif.

`TSK-REF-HR-WLOC-001-01 — Route Discovery Target-First/Fallback` telah dipilih sebagai task coding berikutnya dan berstatus `ready`, bukan `in_progress`. Tidak ada coding pilot yang telah dimulai.

## Aturan

- Daftarkan setiap work item non-trivial sebelum implementasi.
- Pertahankan status registry sinkron dengan dokumen induk.
- Significant child work item mempunyai registry row dan paket sendiri.
- Jangan menghapus baris completed; arsipkan melalui status dan release reference.
- Hanya satu task boleh berstatus `in_progress` pada satu waktu.
