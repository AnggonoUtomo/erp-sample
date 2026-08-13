---
id: ARC-SEOS-WORK-001
title: Standardisasi Paket Dokumentasi per Pekerjaan
document_type: work-item
status: completed
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: ARC-SEOS-WORK-001
related: [DOC-PROP-001]
---

# ARC-SEOS-WORK-001 — Standardisasi Paket Dokumentasi per Pekerjaan

```yaml
id: ARC-SEOS-WORK-001
kind: architecture-change
classification: SIGNIFICANT
status: completed
owner: Pemilik proyek
created_at: 2026-08-13
updated_at: 2026-08-13
parent: null
discovered_by: permintaan-pemilik-proyek-2026-08-13
depends_on: []
blocks: []
related_adrs: []
affected_boundaries: [SEOS]
affected_modules: []
```

## Tujuan

Menetapkan satu paket folder kanonis untuk setiap pekerjaan sehingga dokumen pra-kerja, rencana, task, backlog lokal, konteks, bukti, review, dan penyelesaian dapat ditelusuri tanpa menggunakan daftar status paralel.

## Scope

- aturan paket folder untuk setiap pekerjaan;
- peran `PLAN.md`, `TASKS.md`, dan `BACKLOG.md`;
- metadata boundary dan module;
- komposisi dokumen pra-kerja dan pascakerja;
- supersession `docs/tasks/*` sebagai sumber status aktif;
- audit menyeluruh seluruh dokumentasi yang ada sebelum perubahan;
- sinkronisasi template dan indeks SEOS yang terdampak.

## Non-Scope

- perubahan kode aplikasi, migration, route, permission, atau dependency;
- implementasi DDD-Lite pada modul bisnis;
- penghapusan `HR/IntegrationContracts`;
- migrasi identifier ke ULID;
- perbaikan seluruh isi baseline lama dalam satu pekerjaan ini.

## Alasan Klasifikasi

Perubahan memengaruhi workflow reusable, template semua jenis pekerjaan, dan sumber kebenaran status. Dampaknya lintas repository tetapi tidak mengubah runtime, keamanan, data, atau kontrak aplikasi, sehingga diklasifikasikan `SIGNIFICANT`.

## Persetujuan

```yaml
gate: documentation-structure-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - setiap pekerjaan mempunyai satu folder kanonis
  - folder memuat artefak pra-kerja dan pascakerja
  - plan, task, dan backlog dibedakan secara eksplisit
  - registry tetap menjadi sumber status work item
evidence:
  - persetujuan eksplisit pengguna pada percakapan 2026-08-13
```

## Readiness

```yaml
readiness: READY
approved_by: Pemilik proyek
date: 2026-08-13
notes: Audit awal selesai; scope hanya dokumentasi; tidak ada keputusan aplikasi yang diperlukan.
```

## Indeks Dokumen

| Dokumen | Peran |
|---|---|
| `DOCUMENT-PROPOSAL.md` | proposal reusable dan bukti persetujuan |
| `01-DISCOVERY-RECORD.md` | fakta awal dan pemicu |
| `02-DOCUMENTATION-AUDIT.md` | hasil audit seluruh dokumentasi |
| `03-IMPACT-ASSESSMENT.md` | dampak perubahan governance |
| `PLAN.md` | rencana kontrol pekerjaan |
| `04-IMPLEMENTATION-PLAN.md` | urutan penerapan perubahan |
| `TASKS.md` | satu task aktif dan urutan task |
| `BACKLOG.md` | temuan di luar scope aktif |
| `CONTEXT-PACK.md` | konteks task aktif |
| `DEVIATION-RECORD.md` | penyimpangan rencana |
| `EVIDENCE-MANIFEST.md` | bukti perintah dan hasil aktual |
| `DOCUMENT-AUDIT-MANIFEST.md` | inventaris fingerprint seluruh file dokumentasi |
| `05-VALIDATION-REPORT.md` | hasil verifikasi |
| `REVIEW-REPORT.md` | review lima sumbu dan temuan |
| `06-COMPLETION-REPORT.md` | penutupan dan baseline sync |

## Aksi Berikutnya

Tidak ada task aktif. Kandidat rekonsiliasi produk, engineering, keamanan, operasi, bahasa, dan baseline tetap berada pada `BACKLOG.md` sampai dipilih serta dipromosikan menjadi work item terpisah.
