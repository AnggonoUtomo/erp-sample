# Employee Movements

Module ini menjadi histori resmi perubahan work profile Employees. Vertical slice pertama mendukung transfer DRAFT, histori before/after, dan apply atomik yang efektif hari ini.

## Status

`Vertical slice 01 implemented`.

Slice ini menyediakan form transfer DRAFT, histori assignment sebelum/sesudah, dan apply ke profile Employees dalam satu transaksi database. Apply ditolak bila profile sumber berubah setelah draft dibuat atau tanggal efektif bukan hari ini.

## Urutan baca

1. [Specification](specification.md)
2. [ADR-001: Effective-dated movement](decisions/001-effective-dated-movements.md)
3. [Implementation plan](implementation-plan.md)
4. [Tasks](tasks.md)

## Dokumen terkait

- [HR module guide](../module-guide.md)
- [Employee Contracts](../employee-contracts/README.md)
- [Panduan penggunaan HR](../user-guide/README.md)

## Cara verifikasi

1. Berikan permission `employee-movements.view`, `employee-movements.create`, dan `employee-movements.apply`.
2. Buka `/hr/employee-movements`, buat DRAFT dengan tujuan department, position, atau location baru.
3. Pastikan histori menampilkan nilai before dan after.
4. Klik **Terapkan hari ini**, lalu periksa profile Employees dan status movement `APPLIED`.
5. Jalankan `php artisan test tests/Feature/HREmployeeMovementTest.php`.

## Verifikasi implementasi 2026-07-13

- Pint: lulus.
- ESLint dan Prettier check: lulus.
- TypeScript typecheck: lulus.
- Production build: lulus.
- Backend test suite: 227 test, 794 assertion, seluruhnya lulus.
