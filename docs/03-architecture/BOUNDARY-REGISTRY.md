---
id: BC-REGISTRY-001
title: Registry Boundary
document_type: architecture-registry
status: active
version: 1.0.0
owner: Pemilik proyek
created: 2026-08-12
updated: 2026-08-13
source_work_item: ARC-DDD-LITE-001
related: [ADR-0001, ADR-0002]
---

# Registry Boundary

| ID | Boundary | Tujuan | Status kode | Target | Path | ADR |
|---|---|---|---|---|---|---|
| `BC-CONSOLE-001` | Console | Administrasi, keamanan, observabilitas, dan operasi aplikasi | aktif | tetap | `app/Modules/Console/` | ADR-0001 |
| `BC-HR-001` | HR | Data tenaga kerja, struktur organisasi, serta lifecycle pegawai | aktif | tetap | `app/Modules/HR/` | ADR-0001, ADR-0002 |
| `BC-DMS-001` | DocumentManagement | Penyimpanan privat, versioning, akses, dan delivery dokumen | aktif | tetap | `app/Modules/DocumentManagement/` | ADR-0001 |

## Aturan

1. Modul turunan terdaftar pada `MODULE-CATALOG.md`.
2. Akses lintas boundary menggunakan contract, DTO, event, atau query read-only yang terdokumentasi.
3. Boundary tidak mengekspos Eloquent model sebagai public contract.
4. `app/Integration/` bukan boundary bisnis; ia hanya boleh berisi mekanisme integrasi generik yang terbukti dipakai lintas boundary.
5. Perubahan boundary, penghapusan modul, atau ownership data memerlukan Human Decision Gate.
