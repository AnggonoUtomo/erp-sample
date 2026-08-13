---
id: FTR-PROD-001
title: Rekonsiliasi Baseline Produk dan Requirement
document_type: work-item
status: completed
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: FTR-PROD-001
related: [CAND-DOC-001, AUD-004, ADR-0001, ADR-0002]
---

# FTR-PROD-001 — Rekonsiliasi Baseline Produk dan Requirement

```yaml
id: FTR-PROD-001
kind: feature
classification: SIGNIFICANT
status: completed
owner: Pemilik proyek
created_at: 2026-08-13
updated_at: 2026-08-13
parent: null
discovered_by: CAND-DOC-001
depends_on: []
blocks: []
related_adrs: [ADR-0001, ADR-0002]
affected_boundaries: [Console, HR, DocumentManagement]
affected_modules: []
```

## Tujuan

Menetapkan satu baseline produk aktif yang konsisten antara `PROJECT-BRIEF.md`, `PRD.md`, `SCOPE.md`, `REQUIREMENTS.md`, dan `TRACEABILITY-MATRIX.md`. Baseline menjelaskan kapabilitas bisnis, bukan jumlah atau nama modul, serta membedakan keputusan produk dari bukti perilaku kode.

Paket menggunakan jenis `feature` karena concern utamanya adalah spesifikasi, scope, dan requirement produk. Tidak dibuat kategori work item atau template reusable baru.

## Klasifikasi

`SIGNIFICANT`, karena perubahan menentukan sumber kebenaran produk dan readiness pekerjaan lintas boundary. Tidak ada perubahan aplikasi, data, kontrak, authorization, atau arsitektur dalam work item ini.

## Scope

- Rekonsiliasi Product Brief, PRD, Scope, Requirements, dan Traceability.
- Klasifikasi kapabilitas sebagai `core`, `supporting`, atau `deferred`.
- Pemisahan fakta perilaku aplikasi dari requirement yang disetujui.
- Penghapusan janji jumlah modul, fase, durasi, dan target numerik yang belum disetujui dari baseline produk aktif.
- Pencatatan gap atau target lanjutan pada backlog/work item terpisah.
- Sinkronisasi registry dan inventaris dokumentasi.

## Non-Scope

- Kode aplikasi dan test aplikasi.
- Struktur modul DDD-Lite dan rename modul.
- Kontrak integrasi atau deprecation `HR/IntegrationContracts`.
- Migrasi identifier atau ULID.
- Baseline rinci authorization dan security control.
- Implementasi perubahan perilaku yang ditemukan dari gap dokumentasi.
- Penghapusan dokumen historis.

## Indeks Dokumen

| Dokumen | Peran |
|---|---|
| `01-FEATURE-SPEC.md` | keputusan intent, scope, dan kriteria penerimaan |
| `02-TECHNICAL-DESIGN.md` | desain hubungan antardokumen dan sumber bukti |
| `03-IMPLEMENTATION-PLAN.md` | urutan perubahan dokumentasi |
| `04-TEST-PLAN.md` | pemeriksaan konsistensi dan scope |
| `PLAN.md` | kontrol pekerjaan dan checkpoint |
| `TASKS.md` | status task kanonis |
| `BACKLOG.md` | kandidat tindak lanjut di luar scope |
| `CONTEXT-PACK.md` | konteks terbatas task aktif |
| `DEVIATION-RECORD.md` | deviasi rencana terhadap kondisi aktual |
| `EVIDENCE-MANIFEST.md` | bukti perintah dan hasil aktual |
| `05-REVIEW-REPORT.md` | hasil review |
| `06-COMPLETION-REPORT.md` | hasil, baseline sync, dan status akhir |

## Keputusan Saat Ini / Aksi Berikutnya

Lima baseline telah direkonsiliasi, diverifikasi, direview, dan disinkronkan. Tidak ada task aktif atau perubahan aplikasi.

## Persetujuan dan Readiness

```yaml
gate: product-baseline-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - baseline menggunakan kapabilitas bisnis, bukan jumlah modul
  - kode adalah bukti perilaku saat ini, bukan approval requirement otomatis
  - rencana fase, durasi, dan target numerik lama tidak berlaku tanpa approval baru
  - pekerjaan ini hanya dokumentasi
evidence:
  - konfirmasi eksplisit melalui interview rekonsiliasi CAND-DOC-001
```

```yaml
readiness: READY
approved_by: Pemilik proyek
date: 2026-08-13
notes: Intent, scope, non-scope, sumber bukti, acceptance, area file, risiko, dan verifikasi telah ditetapkan; tidak ada coding.
```
