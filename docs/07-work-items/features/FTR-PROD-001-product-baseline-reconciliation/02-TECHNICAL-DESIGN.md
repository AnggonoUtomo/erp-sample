---
id: DESIGN-FTR-PROD-001
title: Desain Rekonsiliasi Baseline Produk
document_type: documentation-design
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: FTR-PROD-001
related: [SPEC-FTR-PROD-001]
---

# 02 Desain Rekonsiliasi Baseline Produk

## Metadata

```yaml
work_item: FTR-PROD-001
status: approved
owner: Pemilik proyek
last_updated: 2026-08-13
```

## Tujuan

Menetapkan pembagian tanggung jawab lima dokumen agar tidak menjadi sumber kebenaran ganda.

## Desain Dokumen

| Dokumen | Tanggung jawab kanonis |
|---|---|
| `PROJECT-BRIEF.md` | masalah, identitas produk, pengguna tingkat tinggi, outcome, prinsip keberhasilan, batasan |
| `PRD.md` | model kapabilitas, kebutuhan pengguna, prinsip produk, dan acceptance tingkat produk |
| `SCOPE.md` | klasifikasi `core`, `supporting`, `deferred`, serta excluded scope |
| `REQUIREMENTS.md` | FR, NFR, aturan bisnis, prioritas, acceptance, dan status |
| `TRACEABILITY-MATRIX.md` | hubungan outcome → capability → requirement → bukti/work item |

## Aturan Sumber Bukti

1. Keputusan produk berasal dari dokumen approved dan persetujuan manusia.
2. Kode adalah bukti implementasi saat ini.
3. Katalog modul dan ADR adalah sumber arsitektur, bukan target produk.
4. Work item menyimpan alasan perubahan; baseline menyimpan kebenaran aktif.
5. Dokumen historis tidak dihapus atau ditulis ulang.

## Dampak Teknis

Tidak ada perubahan module, data, API, event, kontrak, security control, atau runtime. Perubahan hanya Markdown dan indeks dokumentasi.

## Risiko dan Pertanyaan Terbuka

Nama capability harus stabil walaupun modul direname atau dideprecate. Mapping ke modul dicatat di traceability sebagai bukti aktual dan bukan dependency identitas produk.

## Persetujuan yang Diperlukan

Desain ini tercakup approval intent produk 2026-08-13.

## Keterlacakan

`SPEC-FTR-PROD-001`, `DOCUMENTATION-STANDARD.md`, dan `TRACE-001`.
