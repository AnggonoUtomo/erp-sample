# 01 Proposal Deprecation

## Metadata

```yaml
work_item: DEP-HR-001
status: approved
owner: unassigned
last_updated: 2026-08-13
```

## Tujuan

Menghapus shell modul yang tidak mewakili capability bisnis tanpa menghapus kontrak yang masih bernilai.

## Surface yang Dideprecate

- manifest/provider/permissions `HR/IntegrationContracts`;
- namespace `App\Modules\HR\IntegrationContracts\*`;
- tiga command inspeksi `hr:integration-contracts:*`;
- central contract/event registries sebagai runtime module concern.

## Pengganti yang Diusulkan

| Surface | Target owner |
|---|---|
| Employee profile dan assignment snapshot | `HR/Employees/Integration/` |
| Contract snapshot | `HR/EmployeeContracts/Integration/` |
| Document compliance snapshot | `HR/EmployeeDocuments/Integration/` |
| Event contract | `Integration/Events/` pada producer bisnis |
| Daftar contract/event | katalog SEOS + architecture test |

`IntegrationEventEnvelopeV1` dan `ForbiddenIntegrationFieldGuard` tidak otomatis dipindah ke root shared. Lokasinya diputuskan berdasarkan reuse nyata pada task transisi.

## Perilaku yang Wajib Dipertahankan

- schema version eksplisit;
- additive compatibility untuk v1;
- date semantics terdokumentasi;
- payload minimum dan forbidden sensitive field;
- provider tetap read-only;
- tidak menciptakan REST API, listener mutation, queue, atau outbox spekulatif.

## Compatibility Window

Belum ditetapkan. Karena production consumer namespace lama tidak ditemukan, window dapat pendek, tetapi test dan container binding tetap consumer yang wajib dimigrasikan. Keputusan final menunggu acceptance ADR-0002.

## Persetujuan yang Diperlukan

```yaml
gate: architecture-approval
decision: approved
approver: Pemilik proyek
date: 2026-08-13
conditions:
  - perilaku snapshot dan privacy dipertahankan
  - ownership mapping direview sebelum coding
  - removal aktual menunggu readiness gate
evidence:
  - ADR-0002
  - persetujuan eksplisit Pemilik proyek pada 2026-08-13
```
