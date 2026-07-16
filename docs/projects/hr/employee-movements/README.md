# Employee Movements

Module ini menjadi histori resmi perubahan work profile Employees. Slice saat ini mendukung transfer, promotion, demotion, dan employment change dalam status DRAFT, histori before/after, dan apply atomik yang efektif hari ini.

## Status

`Task 03 implemented`.

Slice ini menyediakan form movement DRAFT, histori assignment/employment sebelum/sesudah, dan apply ke profile Employees dalam satu transaksi database. Apply ditolak bila profile sumber berubah setelah draft dibuat, tanggal efektif bukan hari ini, type movement mengubah field di luar boundary-nya, atau employment type change tidak didukung active contract yang efektif.

## Urutan baca

1. [Specification](specification.md)
2. [ADR-001: Effective-dated movement](decisions/001-effective-dated-movements.md)
3. [ADR-002: Employment type requires effective contract](decisions/002-employment-type-contract-coordination.md)
4. [Implementation plan](implementation-plan.md)
5. [Tasks](tasks.md)

## Dokumen terkait

- [HR module guide](../module-guide.md)
- [Employee Contracts](../employee-contracts/README.md)
- [Panduan penggunaan HR](../user-guide/README.md)

## Cara verifikasi

1. Berikan permission `employee-movements.view`, `employee-movements.create`, dan `employee-movements.apply`.
2. Buka `/hr/employee-movements`, buat DRAFT dengan jenis transfer, promotion, demotion, atau employment change.
3. Pastikan histori menampilkan nilai before dan after.
4. Klik **Terapkan hari ini**, lalu periksa profile Employees dan status movement `APPLIED`.
5. Jalankan `php artisan test tests/Feature/HREmployeeMovementTest.php`.

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
