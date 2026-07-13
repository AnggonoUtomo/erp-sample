# Dokumentasi Starterkit

Dokumentasi dipisahkan berdasarkan relevansi judul dan konteksnya.

## Architecture

Dokumen arsitektur inti starterkit:

- `architecture/starterkit-blueprint.md`
- `architecture/shared-kernel.md`
- `architecture/integration-layer.md`
- `architecture/data-lifecycle.md`

## Guides

Panduan teknis lintas project:

- `guides/project-module-guide.md`

## Projects

Roadmap dan panduan spesifik project:

- `projects/hr/roadmap.md`
- `projects/hr/module-guide.md`
- `projects/accounting/roadmap.md`
- `projects/crm/roadmap.md`
- `projects/document-management/README.md`
- `projects/document-management/roadmap.md`
- `projects/attendance/roadmap.md`
- `projects/payroll/roadmap.md`

## Planning

Catatan rencana umum:

- `planning/PenyusunanProjectKedepan.txt`

## Aturan Update

Setiap perubahan kode yang mengubah generator, struktur module, seeder, route convention, permission, atau arsitektur harus langsung disertai update dokumen terkait.

## Catatan Update Terakhir

### 2026-07-04

- HR `WorkLocations` ditambah latitude/longitude, radius geofence, map selector, dan integrasi konfigurasi Google Maps dari Console System Settings.
- HR `EmploymentStatuses` ditambahkan sebagai master status kerja dengan soft delete, restore, force delete, seeder, permission modular, dan field operasional Attendance/Payroll.
- HR `EmploymentTypes` ditambahkan sebagai master tipe hubungan kerja dengan soft delete, restore, force delete, seeder, permission modular, dan field operasional kontrak, benefit, overtime, serta payroll.
- HR `HRReferenceData` ditambahkan sebagai master referensi umum HR dengan soft delete, restore, force delete, seeder, permission modular, category registry CRUD, dropdown kategori, code manual, dan kategori referensi seperti gender, marital status, education level, religion, bank, serta blood type.
- HR `Employees` ditambahkan sebagai master employee inti dengan avatar Spatie Media Library, link user login, relasi departement/position/job level/work location/status/type, soft delete, restore, force delete, seeder, dan permission modular.
- HR `OrganizationStructures` ditambahkan sebagai master hierarchy organisasi dengan soft delete, restore, force delete, seeder, permission modular, relasi parent/departement/position, dan proteksi child aktif saat delete.
- Console System Settings ditambah menu Google Maps untuk API Key dan Map ID.
- Console `UserManagements` dilengkapi soft delete user, restore, force delete, filter arsip, dan permission `users.restore` serta `users.force-delete`.
- Console `AccessControls` diputuskan tidak memakai soft delete karena `roles` dan `permissions` Spatie adalah konfigurasi authorization aktif.
- Panel preview User Management diperjelas: permission dari role ditampilkan terpisah dari direct permission tambahan.
