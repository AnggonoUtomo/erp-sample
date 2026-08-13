---
id: DOC-ARC-DDD-001-PLAN
title: Rencana Kerja ARC-DDD-LITE-001
document_type: work-plan
status: active
version: 1.3.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-14
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, ADR-0002, FTR-ENG-001]
---

# Rencana Kerja — ARC-DDD-LITE-001

## Outcome

Modul berpindah ke struktur ADR-0001 secara incremental tanpa perubahan perilaku aplikasi.

## Urutan Kontrol

1. Integritas dokumentasi dan tooling.
2. Pilot satu modul kecil setelah paket task memenuhi readiness.
3. Review pilot dan susun dependency order berdasarkan import aktual.
4. Migrasi satu vertical slice pada satu waktu.
5. Jalankan `DEP-HR-001` sebagai work item terpisah.
6. Sinkronkan baseline setelah setiap slice verified.

## Guardrail

- ULID tidak termasuk scope;
- rename dan deprecation tidak diselipkan;
- hanya satu task coding aktif;
- route, permission, response, data, event, dan contract semantics dipertahankan.

## Checkpoint Berikutnya

Baseline produk dan engineering telah direkonsiliasi melalui `FTR-PROD-001` dan `FTR-ENG-001`. Pilot child `REF-HR-WLOC-001` telah selesai dan terverifikasi. Checkpoint berikutnya adalah `TSK-ARC-DDD-LITE-001-03` untuk review hasil pilot serta penyusunan dependency order; statusnya `ready` dan belum ada task `in_progress`.
