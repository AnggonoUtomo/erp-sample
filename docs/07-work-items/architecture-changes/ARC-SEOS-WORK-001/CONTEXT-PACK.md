---
id: DOC-ARC-SEOS-001-CONTEXT
title: Context Pack Standardisasi Paket
document_type: context-pack
status: completed
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: ARC-SEOS-WORK-001
related: [DOC-PROP-001]
---

# Context Pack — ARC-SEOS-WORK-001

## Task Aktif

Tidak ada. Seluruh task dokumentasi telah completed; tidak ada task coding aplikasi yang dipilih.

## Fakta Terverifikasi

- audit awal mencakup 233/233 file fisik di `docs/`;
- proposal `DOC-PROP-001` telah disetujui Pemilik proyek;
- registry adalah sumber status work item;
- hanya satu task lokal boleh `in_progress`;
- `docs/tasks/*` dan Phase-01 lama bertentangan dengan keputusan aktif;
- kode memiliki 28 manifest module dan belum dimigrasikan ke layer target.

## Area yang Diizinkan

- `docs/00-governance/`;
- `docs/02-requirements/TRACEABILITY-MATRIX.md` untuk status stale;
- `docs/03-architecture/SYSTEM-DESIGN.md` untuk referensi status ADR;
- `docs/07-work-items/`;
- `docs/tasks/`;
- laporan audit dan evidence work item ini.

## Area yang Dilarang

- seluruh kode aplikasi, migration, test, config, dependency, route, dan asset;
- perubahan keputusan ADR-0001/ADR-0002;
- aktivasi task DDD-Lite, deprecation, atau ULID.

## Perintah Verifikasi

```powershell
git diff --name-only
git diff --check
```

## Risiko

- mengubah bukti historis seolah tidak pernah berlaku;
- membuat backlog menjadi sumber status kedua;
- menyatakan seluruh baseline sudah akurat hanya karena strukturnya diperbaiki.
