# ADR-002: Employment Fallback dan Calendar-day Due Date

## Status

Accepted

## Date

2026-07-15

## Context

Tidak semua employee memiliki Employee Contract saat onboarding mulai. Draft tetap membutuhkan identity employment period yang deterministik dan task membutuhkan due date yang dapat dihitung sebelum dukungan kalender kerja tersedia.

## Decision

- Jika contract tersedia, onboarding menyimpan `employee_contract_id` yang wajib dimiliki employee terpilih.
- Jika contract belum tersedia, identity MVP memakai `employee_id + start_date` eksplisit.
- `start_date` tidak diinferensikan dari tanggal server.
- Due date Task 04 dihitung dengan calendar day: `start_date + due_offset_days`.
- Nilai offset dan hasil due date disimpan pada task snapshot.
- Active identity memakai `contract:{contract_id}` bila contract tersedia, atau `employee:{employee_id}:start:{start_date}` untuk fallback.
- Fingerprint request dihitung server-side dari employee, optional contract, template, owner, dan start date; retry identik mengembalikan aggregate existing tanpa side effect baru.
- Unique nullable `active_identity_key` menjadi concurrency guard database.

Transition ke status terminal pada Task 10 wajib mengosongkan `active_identity_key` dalam transaction yang sama. Perubahan ke working-day calendar memerlukan ADR baru dan tidak boleh mengubah due date snapshot existing secara otomatis.

## Consequences

- HR dapat memulai draft sebelum contract final tersedia.
- Fallback tetap bisa dibandingkan secara deterministik pada Task 05.
- Hari libur belum memengaruhi due date MVP.
- Contract yang dipilih divalidasi server-side sebagai milik employee.
- Request berbeda untuk identity aktif yang sama ditolak; retry identik tidak menghasilkan task atau audit kedua.

## Related documents

- [Specification](../specification.md)
- [Tasks](../tasks.md)
- [ADR-001](001-checklist-driven-onboarding.md)
