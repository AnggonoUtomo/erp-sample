# ADR-001: Effective-dated Employee Movements

## Status

Accepted — 2026-07-13.

## Context

Jika assignment Employees diubah langsung, kondisi terkini tersedia tetapi alasan dan nilai sebelumnya hilang. Movement harus menjaga histori tanpa membiarkan draft mengubah profile.

## Decision

Employees tetap menyimpan current profile. EmployeeMovements menyimpan immutable before/after snapshot dan effective date. Hanya aksi Apply yang memperbarui profile; update movement dan employee terjadi dalam satu transaction. Slice pertama hanya menerima effective date hari ini. Apply ditolak bila current profile tidak lagi sama dengan before snapshot.

Slice berikutnya memperluas snapshot ke `job_level_id` agar promotion/demotion tetap melewati jalur movement yang sama. Transfer tidak boleh mengubah job level; promotion/demotion wajib mengubah job level aktif.

Task 03 memperluas snapshot ke `employment_status_id` dan `employment_type_id`. Employment change menjadi movement type terpisah agar perubahan status/type tidak terselip di transfer atau promotion/demotion.

Task 04 mengizinkan effective date masa depan. Draft tidak mengubah profile sampai manual apply atau command scheduler menerapkannya setelah due date.

Task 05 menambahkan approval gate sehingga apply hanya menerima movement yang sudah `APPROVED`.

## Consequences

- Histori transfer dapat diaudit dan current profile tetap cepat dibaca.
- Draft aman dan belum memengaruhi Employees.
- Promotion/demotion memiliki histori before/after yang sama kuatnya dengan transfer.
- Employment status/type memiliki histori before/after tanpa mengubah kontrak secara diam-diam.
- Future scheduling tersedia melalui command due-date; backdate tetap non-scope.
- Approval menjadi checkpoint eksplisit sebelum profile Employees berubah.
- Contract tidak berubah otomatis pada transfer.
