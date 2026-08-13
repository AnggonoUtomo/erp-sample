# 02 Proposal Boundary dan Struktur

## Metadata

```yaml
work_item: ARC-DDD-LITE-001
status: approved
owner: unassigned
last_updated: 2026-08-13
```

## Struktur Target yang Disetujui

ADR-0001 menetapkan lokasi baku dengan folder minimal:

| Concern | Lokasi target bila diperlukan |
|---|---|
| Use case/orchestration | `Application/Actions`, `Application/Services`, `Application/Queries` |
| DTO internal use case | `Application/DTOs` |
| Aturan domain nyata | `Domain/*` |
| Eloquent dan detail teknis | `Infrastructure/Models`, `Infrastructure/Providers`, `Infrastructure/External` |
| HTTP | `Presentation/Http/Controllers`, `Presentation/Http/Requests`, `Presentation/Http/Resources` |
| Route module | `Presentation/Routes` |
| Kontrak/event lintas modul | `Integration/Contracts`, `Integration/DTOs`, `Integration/Events` |
| Persistence assets | `Database/*` |
| Test milik modul | `Tests/*` |

Folder kosong dan abstraksi spekulatif dilarang.

## Boundary Bisnis

Boundary Console, HR, dan DocumentManagement tetap dipertahankan. Evaluasi tanggung jawab modul terdapat pada katalog aktif. Tidak ada usulan merge.

## Proposal Ownership Kontrak HR

| Kontrak shell saat ini | Pemilik bisnis target |
|---|---|
| Employee profile snapshot | `HR/Employees` |
| Employee assignment snapshot | `HR/Employees` |
| Employee contract snapshot | `HR/EmployeeContracts` |
| Employee document compliance snapshot | `HR/EmployeeDocuments` |
| Event versioned | Modul producer masing-masing |

Proposal ini telah diterima melalui ADR-0002. Implementasinya tetap membutuhkan readiness `DEP-HR-001`. Semantics provider lama tidak boleh diubah saat dipindahkan.

## Ownership Data

Ownership tabel aktual berada pada `docs/04-design/DATABASE-DESIGN.md`. Restrukturisasi file tidak mengubah ownership data.

## Persetujuan

- [x] Struktur DDD-Lite adaptif — ADR-0001 accepted.
- [x] Boundary Console/HR/DocumentManagement dipertahankan.
- [x] Tidak ada merge tanpa tanggung jawab bisnis yang tumpang tindih.
- [x] Pemetaan final contract ownership — ADR-0002 accepted.
- [ ] Rename kandidat — belum memiliki work item dan belum disetujui untuk implementasi.
