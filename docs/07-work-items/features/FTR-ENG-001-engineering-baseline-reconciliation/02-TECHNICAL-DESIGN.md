---
id: DESIGN-FTR-ENG-001
title: Desain Rekonsiliasi Baseline Engineering
document_type: documentation-design
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: FTR-ENG-001
related: [SPEC-FTR-ENG-001, ENG-TECH-001, ENG-TEST-001]
---

# Desain Rekonsiliasi Baseline Engineering

## Pembagian Status

Setiap concern dinyatakan sebagai:

- aktual: dibuktikan repository/command;
- target: disetujui ADR atau keputusan manusia;
- deferred: belum menjadi komitmen implementasi;
- rekomendasi: belum menjadi quality gate.

Klaim lama tanpa bukti tidak dipindahkan ke baseline aktif.

## Ownership Dokumen

| Concern | Sumber kebenaran |
|---|---|
| Stack, command, interface/auth aktual, default driver, standard aktif, CI dan batas operasional | TECHNICAL-SPEC.md |
| Level test, lokasi aktual/target, environment test, command, CI test, evidence dan deferred gate | TESTING-STRATEGY.md |
| Struktur DDD-Lite target | ADR-0001 |
| Ownership kontrak integrasi | ADR-0002 dan DEP-HR-001 |
| Requirement non-functional | REQUIREMENTS.md dan TRACEABILITY-MATRIX.md |
| Status pekerjaan | WORK-ITEM-REGISTRY.md dan TASKS.md lokal |

## Rekonsiliasi Konflik

| Klaim lama | Bukti/keputusan | Perlakuan |
|---|---|---|
| Sanctum dan /api/v1 aktif | package/route tidak mendukung; owner memilih web/session | deferred |
| Redis/S3/staging/monitoring production | hanya default/config development ditemukan | deferred |
| Seluruh test module-local | test aktual masih di tests/ | target incremental |
| Pest/PHPStan aktif | dependency tidak tersedia | rekomendasi/deferred |
| strict_types/final/readonly/PHPDoc wajib | tidak ditegakkan konsisten | rekomendasi |
| Coverage dan performa numerik | tidak ada baseline/approval | deferred |
| CI branch sesuai branch aktif | workflow develop/main; branch aktif dev | gap kandidat |

## Dampak Teknis

Tidak ada perubahan runtime. Perubahan hanya mengganti klaim normatif pada baseline dokumentasi dan memperbaiki keterlacakan ke work item/ADR.

## ADR

ADR baru tidak diperlukan. Work item menegaskan ADR-0001 dan ADR-0002 tanpa membuat boundary, contract, atau keputusan arsitektur baru.
