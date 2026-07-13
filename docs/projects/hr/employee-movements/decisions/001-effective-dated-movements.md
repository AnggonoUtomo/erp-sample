# ADR-001: Effective-dated Employee Movements

## Status

Accepted — 2026-07-13.

## Context

Jika assignment Employees diubah langsung, kondisi terkini tersedia tetapi alasan dan nilai sebelumnya hilang. Movement harus menjaga histori tanpa membiarkan draft mengubah profile.

## Decision

Employees tetap menyimpan current profile. EmployeeMovements menyimpan immutable before/after snapshot dan effective date. Hanya aksi Apply yang memperbarui profile; update movement dan employee terjadi dalam satu transaction. Slice pertama hanya menerima effective date hari ini. Apply ditolak bila current profile tidak lagi sama dengan before snapshot.

## Consequences

- Histori transfer dapat diaudit dan current profile tetap cepat dibaca.
- Draft aman dan belum memengaruhi Employees.
- Future scheduling serta backdate memerlukan desain scheduler/reconciliation terpisah.
- Contract tidak berubah otomatis pada transfer.
