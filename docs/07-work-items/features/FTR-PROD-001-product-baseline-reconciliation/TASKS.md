---
id: DOC-FTR-PROD-001-TASKS
title: Task Rekonsiliasi Baseline Produk
document_type: work-item-tasks
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: FTR-PROD-001
related: [CAND-DOC-001, AUD-004]
---

# Task — FTR-PROD-001

## Urutan

| Urutan | Task ID | Tujuan | Bergantung pada | Status |
|---:|---|---|---|---|
| 1 | `TSK-FTR-PROD-001-01` | Inventaris sumber dan konflik | - | completed |
| 2 | `TSK-FTR-PROD-001-02` | Konfirmasi intent dan keputusan produk | 1 | completed |
| 3 | `TSK-FTR-PROD-001-03` | Rekonsiliasi baseline produk dan requirement | 2 | completed |
| 4 | `TSK-FTR-PROD-001-04` | Verifikasi dan review dokumentasi | 3 | completed |
| 5 | `TSK-FTR-PROD-001-05` | Sinkronisasi dan penyelesaian work item | 4 | completed |

## Task Terakhir

Tidak ada task aktif. Bagian berikut mempertahankan context task rekonsiliasi yang telah selesai.

### `TSK-FTR-PROD-001-03` — Rekonsiliasi baseline produk dan requirement

```yaml
status: completed
owner: Pemilik proyek
size: medium
references: [PROJECT-BRIEF, PRD, SCOPE, REQUIREMENTS, TRACE-001, MOD-CATALOG-001, ADR-0001, ADR-0002]
```

#### Tujuan

Menulis ulang baseline aktif berdasarkan keputusan yang disetujui tanpa mengubah kode atau menghapus bukti historis.

#### Area yang Diizinkan

- `docs/01-product/PROJECT-BRIEF.md`
- `docs/01-product/PRD.md`
- `docs/01-product/SCOPE.md`
- `docs/02-requirements/REQUIREMENTS.md`
- `docs/02-requirements/TRACEABILITY-MATRIX.md`
- paket `FTR-PROD-001`
- registry, backlog sumber, dan inventory dokumentasi

#### Area yang Dilarang

- `app/**`, `tests/**`, `database/**`, `resources/**`, `routes/**`
- isi ADR, boundary, module catalog, kontrak, dan migration work item
- authorization matrix dan implementasi security

#### Kriteria Penerimaan

- [x] Lima baseline konsisten dan berstatus disetujui.
- [x] Capability menjadi unit scope; jumlah modul hanya referensi arsitektur.
- [x] Fakta kode dan keputusan produk dipisahkan secara eksplisit.
- [x] Rencana fase/durasi lama tidak menjadi baseline aktif.
- [x] Target NFR tanpa bukti dipindahkan menjadi kandidat tindak lanjut.
- [x] Requirement memiliki ID, acceptance, status, dan traceability.
- [x] Tidak ada file aplikasi yang berubah.

#### Verifikasi

```powershell
rg -n "28 modul|27 modul|18 modul|8 bulan|Phase [1-4]|<200ms|99\.9%|1000 concurrent|>85%|Zero data loss" docs/01-product docs/02-requirements
git diff --check
git status --short
```
