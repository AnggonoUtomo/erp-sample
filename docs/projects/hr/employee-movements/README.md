# Employee Movements

Module ini menjadi histori resmi perubahan work profile Employees. Slice saat ini mendukung transfer, promotion, demotion, dan employment change dalam status DRAFT, histori before/after, dan apply atomik yang efektif hari ini.

## Status

`Task 05 implemented`.

Slice ini menyediakan form movement DRAFT, approval gate, histori assignment/employment sebelum/sesudah, cancellation, archive/restore untuk cancelled movement, command scheduler due-date, event integration v1, dan apply ke profile Employees dalam satu transaksi database. Apply ditolak bila movement belum approved, profile sumber berubah setelah draft dibuat, movement belum due, type movement mengubah field di luar boundary-nya, atau employment type change tidak didukung active contract yang efektif.

## Urutan baca

1. [Specification](specification.md)
2. [ADR-001: Effective-dated movement](decisions/001-effective-dated-movements.md)
3. [ADR-002: Employment type requires effective contract](decisions/002-employment-type-contract-coordination.md)
4. [ADR-003: Future-effective scheduler and cancellation](decisions/003-future-effective-scheduler-and-cancellation.md)
5. [ADR-004: Approval, archive, and applied event](decisions/004-approval-archive-and-applied-event.md)
6. [Implementation plan](implementation-plan.md)
7. [Tasks](tasks.md)

## Dokumen terkait

- [HR module guide](../module-guide.md)
- [Employee Contracts](../employee-contracts/README.md)
- [Panduan penggunaan HR](../user-guide/README.md)

## Cara verifikasi

1. Berikan permission `employee-movements.view`, `employee-movements.create`, dan `employee-movements.apply`.
2. Buka `/hr/employee-movements`, buat DRAFT dengan jenis transfer, promotion, demotion, atau employment change.
3. Pastikan histori menampilkan nilai before dan after.
4. Approve movement DRAFT.
5. Untuk movement approved dan due, klik **Terapkan jika due**, lalu periksa profile Employees dan status movement `APPLIED`.
6. Untuk movement future atau tidak jadi, coba cancel dengan alasan dan pastikan status menjadi `CANCELLED`.
7. Archive cancelled movement bila diperlukan, lalu restore melalui route backend jika perlu.
8. Jalankan `php artisan hr:employee-movements:apply-due --date=YYYY-MM-DD --dry-run`.
9. Jalankan `php artisan test tests/Feature/HREmployeeMovementTest.php`.

## Verifikasi implementasi 2026-07-13

- Pint: lulus.
- ESLint dan Prettier check: lulus.
- TypeScript typecheck: lulus.
- Production build: lulus.
- Backend test suite: 227 test, 794 assertion, seluruhnya lulus.

## Verifikasi implementasi 2026-07-17

- Employee Movement focused test: 6 test, 29 assertion, seluruhnya lulus.
- Scope tambahan: promotion/demotion mengubah job level; transfer dijaga agar tidak mengubah job level.

## Verifikasi implementasi 2026-07-17 — Task 03

- Employee Movement focused test: 8 test, 41 assertion, seluruhnya lulus.
- Scope tambahan: employment status/type change; employment type change wajib punya active contract yang efektif.

## Verifikasi implementasi 2026-07-17 — Task 04

- Employee Movement focused test: 10 test, 54 assertion, seluruhnya lulus.
- Scope tambahan: future-effective DRAFT, cancellation, dan command `hr:employee-movements:apply-due`.

## Verifikasi implementasi 2026-07-17 — Task 05

- Employee Movement focused test: 11 test, 74 assertion, seluruhnya lulus.
- Scope tambahan: approval gate, archive/restore cancelled movement, dan event `EmployeeMovementAppliedV1`.
