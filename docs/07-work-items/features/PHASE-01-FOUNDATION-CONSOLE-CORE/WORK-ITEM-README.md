---
id: PHASE-01-FOUNDATION-CONSOLE-CORE
title: Paket Perencanaan Foundation dan Console Core Lama
document_type: historical-work-item
status: superseded
version: 1.0.0
owner: unassigned
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, MIG-ID-001, ARC-SEOS-WORK-001]
---

# Phase 01 Foundation dan Console Core — Superseded

Seluruh dokumen dalam folder ini adalah bukti rencana sebelum keputusan final ADR-0001 dan pemisahan `MIG-ID-001`. Dokumen tidak menjadi sumber kebenaran aktif dan seluruh task `ready` di dalamnya dibatalkan oleh supersession ini.

Konflik utama:

- delapan layer diwajibkan, sedangkan ADR-0001 menetapkan struktur minimal sesuai kebutuhan;
- ULID digabung dengan generator dan module conversion, sedangkan `MIG-ID-001` memisahkan serta menundanya;
- public `/api/v1` dan Shared Kernel spekulatif direncanakan tanpa baseline perilaku yang cukup;
- beberapa task besar mencampur restrukturisasi, schema, authorization, API, dan fitur.

Sumber aktif pengganti:

- ADR-0001 untuk struktur DDD-Lite;
- `ARC-DDD-LITE-001` untuk restrukturisasi incremental;
- `MIG-ID-001` untuk keputusan identifier;
- `WORK-ITEM-REGISTRY.md` untuk status pekerjaan.

Dokumen lama tidak dihapus atau ditulis ulang agar riwayat keputusan tetap dapat diaudit.
