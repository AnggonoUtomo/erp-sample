---
id: BL-2026-001
title: Snapshot Dokumentasi Pra-SEOS
document_type: baseline-snapshot
status: proposed
version: 0.1.0
owner: Pemilik proyek
created: 2026-08-13
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, ADR-0002]
---

# Manifest Baseline Pra-SEOS

## Sumber

```yaml
source_branch: main
source_commit: aab3c87a88ccdda64c95051ec72b431648e0ecdf
commit_date: 2026-07-22T15:03:44+07:00
commit_subject: "docs: finalize console global search checkpoint"
snapshot_type: historical-evidence
active_source_of_truth: false
```

## Tujuan

Menjaga keputusan, requirement, checkpoint, dan catatan perilaku sebelum SEOS sebagai bukti historis. Baseline SEOS aktif boleh menggantikan keputusan lama, tetapi tidak boleh menghapus jejak asalnya.

Perbandingan `main..dev` menunjukkan 196 file dokumentasi terhapus. File tersebut tetap dapat dibaca secara immutable dari commit sumber, misalnya:

```bash
git show aab3c87a88ccdda64c95051ec72b431648e0ecdf:docs/projects/hr/integration-contracts/decisions/001-stable-hr-integration-contracts.md
```

## Keputusan Historis Utama

- Console operational foundation dan global-search contracts.
- DMS private storage, upload security, single-server exception, dan one-time delivery.
- Employee Contracts effective-dated dan versioned snapshot boundary.
- Employee Documents metadata/storage boundary, archive semantics, reference contract, attachment adapter, dan secure delivery authority.
- Employee Movements effective dating, employment-type coordination, scheduler/cancellation, approval/archive/event.
- HR Reports read-only boundary.
- HR Integration Contracts versioned/minimum-PII boundary.
- Onboarding dan Offboarding lifecycle/checklist/effective-dated decisions.
- Project baseline decisions tentang contract source of truth dan risk-first remediation.

## Aturan Penggunaan

1. Dokumen pada commit sumber adalah evidence, bukan baseline aktif.
2. Jika keputusan lama masih berlaku, baseline aktif wajib menyatakan prinsip yang dipertahankan.
3. Jika diganti, ADR baru wajib menyebut dokumen/commit yang digantikan.
4. Snapshot menjadi immutable setelah approval; koreksi dibuat sebagai errata/snapshot pengganti.

## Persetujuan

Status masih `proposed` sampai daftar scope dan commit sumber direview manusia.
