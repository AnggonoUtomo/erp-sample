# Finalization Security Review - Task 13

## Status

`PASSED` pada 2026-07-16.

## Tujuan

Review ini memastikan finalisasi Employee Offboarding hanya bisa dilakukan lewat boundary server yang benar, memakai snapshot Offboarding yang terkunci, dan menolak input yang mencoba memilih Employee, Contract, status akhir, alasan, tanggal efektif, atau actor lain.

## Trust boundary dan aset

Input tidak tepercaya:

- Route parameter `offboarding`.
- Form field `business_date`.
- Extra payload yang dikirim client, termasuk `employee_id`, `contract_id`, `target_employment_status_id`, `effective_date`, `reason`, dan `actor_id`.
- Session user dan permission yang harus selalu divalidasi server-side.

Aset yang dilindungi:

- Profile Employee dan status employment.
- Contract aktif Employee.
- Lifecycle Offboarding.
- Audit log finalization.
- Integritas relasi Employee, Contract, dan Offboarding.

## Denial matrix

| Case | Expected result |
| --- | --- |
| Guest mengakses mutation finalization | Redirect ke login HR. |
| Viewer tanpa permission `hr.offboardings.finalize` | `403 Forbidden`. |
| Officer tanpa permission finalize | `403 Forbidden`. |
| Unknown Offboarding ID | `404 Not Found`. |
| Archived Offboarding | Ditolak fail-closed dengan generic validation error. |
| Employee snapshot stale | Ditolak fail-closed dengan generic validation error. |
| Contract snapshot stale | Ditolak fail-closed dengan generic validation error. |
| Final status tidak valid untuk termination | Ditolak fail-closed dengan generic validation error. |
| Payload mencoba override Employee/Contract/status/reason/actor | Diabaikan; service hanya memakai locked server snapshot. |
| Mutation route tanpa auth/policy middleware | Test route inventory gagal. |

## Keputusan hardening

- Finalization hanya menerima `business_date` sebagai input operasional yang eksplisit.
- Actor finalization selalu berasal dari session user.
- Employee, Contract, final status, reason, dan effective date berasal dari snapshot Offboarding yang terkunci.
- Owner module melakukan finalization dalam transaksi database dan memakai row lock.
- Rejection untuk archived, stale owner data, incomplete required task, invalid target, dan invalid actor menggunakan satu pesan generik.
- Error generik tidak mengekspos nama Employee, nomor kontrak, nama tabel, class internal, atau detail lock.
- Frontend hanya UX boundary; authorization, invariant, dan source of truth tetap di backend.
- Finalization tidak melakukan hard delete dan tidak menulis langsung ke Attendance atau Payroll.

## Route inventory

Semua mutation route `hr.offboardings.*` wajib memiliki middleware `auth` dan policy middleware sesuai action. Route finalization wajib memakai `can:finalize,offboarding`.

Inventory ini diuji supaya penambahan route mutation baru tanpa guard langsung terlihat saat quality gate berjalan.

## Verification

Perintah yang dijalankan:

```bash
php artisan test --filter=OffboardingAuthorizationMatrix
php artisan test --filter=OffboardingFinalization
php artisan test --filter=MutationRouteAuthorization
```

## Deferred

- Checkpoint D belum ditutup.
- Integration event downstream ke Attendance, Payroll, Accounting, dan DMS belum dibuka.
- Audit export/reporting khusus finalization dapat dibuat setelah kebutuhan operasionalnya jelas.
