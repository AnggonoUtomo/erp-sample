---
id: TRACE-001
title: Matriks Keterlacakan Aktual
document_type: traceability-matrix
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, ADR-0002, DEP-HR-001, MIG-ID-001]
---

# Matriks Keterlacakan Aktual

## Keputusan ke Bukti

| Concern | Keputusan/dokumen aktif | Bukti implementasi saat audit | Status |
|---|---|---|---|
| DDD-Lite adaptif | ADR-0001 | Struktur modul masih datar | target diterima, belum diimplementasikan |
| Katalog modul | `MODULE-CATALOG.md` | 28 file `module.php` | terverifikasi |
| Ownership kontrak integrasi | ADR-0002 | kontrak tersebar pada modul bisnis dan shell pusat | accepted |
| Deprecation shell integrasi HR | `DEP-HR-001` | tidak ada production import di luar modul; test imports ada | approved, belum ready |
| Identifier database | `DATABASE-DESIGN.md`, `MIG-ID-001` | `$table->id()` / `foreignId()` | bigint aktif; ULID ditunda |
| Interface HTTP | `API-SPEC.md` | web/module `routes.php`; tidak ada route IntegrationContracts | bukti file; runtime belum terverifikasi |
| Dokumen historis | `BL-2026-001-pre-seos` | commit `aab3c87a88ccdda64c95051ec72b431648e0ecdf` | snapshot diusulkan |

## Modul ke Data

Kepemilikan tabel authoritative dicatat pada `docs/04-design/DATABASE-DESIGN.md`. Katalog modul tanpa tabel tetap dicatat pada `docs/03-architecture/MODULE-CATALOG.md`.

## Modul ke Kontrak dan Event

- Contract aktual: `docs/04-design/INTEGRATION-CATALOG.md`.
- Event integrasi aktual: `docs/04-design/EVENT-CATALOG.md`.
- Nama pada manifest/registry tanpa publisher atau consumer tidak diberi status aktif.

## Test dan Verifikasi

| Area | Bukti tersedia | Keterbatasan |
|---|---|---|
| Contract HR Integration | test `HRIntegration*` dan contract test module-owned | test belum dimigrasikan ke owner target |
| Module generator | command dipulihkan dari commit sumber; `MakeModuleCommandTest` 4 test/23 assertion | verified untuk perilaku lama |
| Module validation | `php artisan module:validate` | verified; seluruh kontrak modul valid |
| Backend | 101 file Feature dan 5 file Unit ditemukan | test generator lulus; full suite tidak dijalankan pada task restore exact |
| Frontend | script lint/typecheck tersedia | pemeriksaan sebelumnya melewati batas waktu; bukan bukti lulus |

## Perubahan Terdaftar

| Work item | Tanggal | Scope | Status |
|---|---|---|---|
| `ARC-DDD-LITE-001` | 2026-08-12 | Struktur DDD-Lite seluruh modul | in_progress; task tooling selesai, tidak ada task coding aktif |
| `DEP-HR-001` | 2026-08-13 | Deprecation `HR/IntegrationContracts` | approved, belum ready |
| `MIG-ID-001` | 2026-08-13 | Evaluasi/migrasi primary key ke ULID | deferred |
