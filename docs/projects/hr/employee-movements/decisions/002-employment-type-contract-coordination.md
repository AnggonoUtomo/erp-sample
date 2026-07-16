# ADR-002: Employment Type Change Requires Effective Contract

## Status

Accepted — 2026-07-17.

## Context

`Employees.employment_type_id` adalah current profile yang dipakai oleh HR dan downstream module. Di sisi lain, masa berlaku hubungan kerja formal dikelola oleh `EmployeeContracts`. Jika Employee Movements boleh mengubah employment type tanpa melihat kontrak, profile employee dapat menyatakan tipe kerja yang tidak memiliki dasar kontraktual.

## Decision

Employee Movements menambahkan movement type `EMPLOYMENT_CHANGE` untuk perubahan `employment_status_id` dan/atau `employment_type_id`.

Aturan v1:

- `EMPLOYMENT_CHANGE` hanya boleh mengubah employment status/type.
- Target employment status/type harus aktif.
- Jika `employment_type_id` berubah, harus ada contract `ACTIVE` milik employee yang:
  - memiliki `employment_type_id` sama dengan target,
  - `start_date <= effective_date`,
  - dan `end_date` kosong atau `end_date >= effective_date`.
- Movement hanya membaca contract sebagai guard. Movement tidak membuat, mengaktifkan, supersede, terminate, atau cancel contract.

## Alternatives Considered

### Movement otomatis membuat atau mengubah contract

Ditolak untuk MVP karena lifecycle contract sudah memiliki invariant sendiri: interval non-overlap, activation, termination, cancellation, supersede, archive/restore, dan snapshot integration.

### Employment type bebas berubah tanpa contract

Ditolak karena dapat membuat profile Employees tidak konsisten dengan contract aktif.

### Hanya Employee Contracts yang mengubah Employees.employment_type_id

Ditunda. Pendekatan ini lebih ketat, tetapi membutuhkan event/listener atau orchestration tambahan. Untuk slice ini, movement tetap menjadi jalur perubahan profile, dengan contract sebagai guard.

## Consequences

- Perubahan employment type dapat diaudit melalui Employee Movements dan tetap sinkron dengan contract aktif.
- Employee Contracts tetap pemilik lifecycle kontrak.
- Future scheduler harus mengulang guard contract pada saat apply, bukan hanya saat draft dibuat.
- Jika organisasi ingin contract activation otomatis mengubah employee type, desain baru harus dibuat sebagai ADR lanjutan.
