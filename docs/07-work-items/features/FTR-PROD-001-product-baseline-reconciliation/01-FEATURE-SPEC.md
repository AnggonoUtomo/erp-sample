---
id: SPEC-FTR-PROD-001
title: Spesifikasi Rekonsiliasi Baseline Produk
document_type: feature-specification
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: FTR-PROD-001
related: [CAND-DOC-001, AUD-004, ADR-0001, ADR-0002]
---

# 01 Spesifikasi Rekonsiliasi Baseline Produk

## Metadata

```yaml
work_item: FTR-PROD-001
status: approved
owner: Pemilik proyek
last_updated: 2026-08-13
```

## Tujuan

Menghasilkan baseline produk yang menyatakan produk apa yang dibangun, untuk konteks organisasi mana, kapabilitas apa yang termasuk, requirement apa yang disetujui, dan bagaimana semuanya dapat diverifikasi.

## Input dan Referensi

- Product Brief, PRD, Scope, Requirements, dan Traceability lama.
- Temuan `AUD-004` dan kandidat `CAND-DOC-001`.
- Perilaku manifest dan route pada kode sebagai bukti keadaan saat ini.
- ADR-0001, ADR-0002, Module Catalog, Boundary Registry, dan System Design.
- Jawaban interview Pemilik proyek pada 2026-08-13.

## Keputusan / Hasil

1. Produk adalah sistem operasional internal yang berpusat pada HR, pengelolaan dokumen, dan administrasi sistem. Istilah ERP adalah payung proyek, bukan janji finance, inventory, procurement, sales, payroll, atau kapabilitas ERP lain.
2. Baseline produk menggunakan kapabilitas bisnis; jumlah dan nama modul hanya fakta arsitektur.
3. Kode membuktikan perilaku aplikasi saat ini, tetapi tidak otomatis mengesahkan requirement.
4. Perilaku eksisting menjadi baseline kompatibilitas sampai requirement menyatakan perubahan; perilaku tanpa nilai bisnis jelas dievaluasi terpisah.
5. Kapabilitas diklasifikasikan sebagai `core`, `supporting`, atau `deferred`.
6. Urutan rilis diputuskan per work item berdasarkan nilai bisnis, dependensi, dan readiness. Fase serta durasi lama tidak menjadi komitmen aktif.
7. Atribut kualitas tetap wajib. Angka target hanya menjadi requirement setelah baseline, kebutuhan, metode verifikasi, dan approval tersedia.
8. Work item ini hanya mengubah dokumentasi. Gap implementasi dipromosikan menjadi pekerjaan terpisah.

## Kriteria Penerimaan

| ID | Kriteria |
|---|---|
| `AC-PROD-001` | Product Brief, PRD, Scope, Requirements, dan Traceability memakai identitas serta istilah yang konsisten. |
| `AC-PROD-002` | Capability ID menjadi unit scope dan terhubung ke requirement. |
| `AC-PROD-003` | Jumlah modul hanya dirujuk ke katalog arsitektur. |
| `AC-PROD-004` | Fakta kode, keputusan produk, asumsi, dan kandidat tindak lanjut dapat dibedakan. |
| `AC-PROD-005` | Fase, durasi, dan target kualitas numerik tanpa approval tidak menjadi baseline aktif. |
| `AC-PROD-006` | Seluruh requirement aktif memiliki ID, prioritas, kriteria penerimaan, status, dan traceability. |
| `AC-PROD-007` | Tidak ada kode, kontrak, data, authorization, atau perilaku aplikasi yang berubah. |

## Risiko dan Pertanyaan Terbuka

Prioritas rilis, persona rinci, authorization, serta ambang kualitas numerik belum disetujui dan dicatat pada backlog.

## Persetujuan yang Diperlukan

Persetujuan produk telah diberikan Pemilik proyek pada 2026-08-13. Tidak ada Human Decision Gate arsitektur baru karena ADR dan boundary tidak diubah.

## Keterlacakan

`CAND-DOC-001 → FTR-PROD-001 → AC-PROD-001..007 → lima baseline aktif → EVIDENCE-MANIFEST.md`.
