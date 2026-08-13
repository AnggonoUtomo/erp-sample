---
id: TEST-FTR-PROD-001
title: Rencana Pengujian Rekonsiliasi Baseline Produk
document_type: test-plan
status: approved
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: FTR-PROD-001
related: [AC-PROD-001, AC-PROD-002, AC-PROD-003, AC-PROD-004, AC-PROD-005, AC-PROD-006, AC-PROD-007]
---

# 04 Rencana Pengujian Dokumentasi

## Metadata

```yaml
work_item: FTR-PROD-001
status: approved
owner: Pemilik proyek
last_updated: 2026-08-13
```

## Pemeriksaan

| ID | Pemeriksaan | Ekspektasi |
|---|---|---|
| `DOC-TST-001` | istilah modul/fase/durasi/target spekulatif pada lima baseline | tidak ditemukan sebagai requirement aktif |
| `DOC-TST-002` | status metadata dan baris requirement | approved/active sesuai peran; tidak ada Draft |
| `DOC-TST-003` | capability dan requirement ID | unik dan seluruh mapping valid |
| `DOC-TST-004` | heading, frontmatter, code fence, dan link lokal | valid |
| `DOC-TST-005` | scope Git | hanya file `docs/**` |
| `DOC-TST-006` | whitespace | `git diff --check` lulus |
| `DOC-TST-007` | inventory | seluruh file dokumentasi tercatat |

## Pengujian Aplikasi

Tidak dijalankan karena tidak ada kode, konfigurasi runtime, schema, atau test aplikasi yang berubah. Pembatasan ini harus dicatat, bukan dianggap sebagai bukti bahwa aplikasi lulus test.

## Keterlacakan

Hasil aktual dicatat pada `EVIDENCE-MANIFEST.md` dan `05-REVIEW-REPORT.md`.
