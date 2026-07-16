# Checkpoint D - Effective Exit

**Status:** PASS

**Tanggal:** 2026-07-16

**Verified baseline:** working tree berbasis `3fb67d7`

## Scope yang diverifikasi

Checkpoint ini menutup Task 11-13:

- employment termination boundary milik owner module Employees dan Employee Contracts;
- finalization `READY_FOR_EXIT -> COMPLETED`;
- update Employee dan optional Contract secara effective-dated;
- transaction, row lock, retry, audit, rollback, dan denial matrix;
- route mutation inventory untuk seluruh mutation Offboarding.

Checkpoint ini belum mencakup archive/restore case, due command, filter operasional lanjutan, atau integration event downstream.

## Evidence quality gate

| Gate | Hasil |
| --- | --- |
| `php artisan module:validate` | PASS |
| `php artisan test --filter=OffboardingTerminationContract` | PASS |
| `php artisan test --filter=OffboardingFinalization` | PASS - 6 tests, 41 assertions |
| `php artisan test --filter=OffboardingAuthorizationMatrix` | PASS - 4 tests, 70 assertions |
| `php artisan test --filter=MutationRouteAuthorization` | PASS - 1 test, 126 assertions |
| `php artisan test --filter=Offboarding` | PASS - 64 tests, 659 assertions |
| `vendor\bin\pint app/Modules/HR/Offboardings/Services/OffboardingFinalizationService.php tests/Feature/OffboardingAuthorizationMatrixTest.php` | PASS |
| `git diff --check` | PASS |

## Effective exit contract

| Area | Contract yang dibuktikan |
| --- | --- |
| State source | Hanya Offboarding `READY_FOR_EXIT` yang bisa difinalisasi. |
| Business date | Finalization ditolak sebelum `exit_date`; retry setelah `COMPLETED` menjadi no-op idempotent. |
| Employee mutation | Employee dinonaktifkan melalui gateway owner module Employees dengan target final employment status dari snapshot Offboarding. |
| Contract mutation | Optional Contract diakhiri melalui gateway owner module Employee Contracts bila snapshot Contract tersedia. |
| Evidence | Offboarding menyimpan `finalized_by`, `finalized_at`, dan `finalization_business_date`. |
| Audit | Employee, Contract, dan Offboarding audit commit bersama atau seluruhnya rollback. |
| Concurrency | Row lock menserialisasi request bersamaan sehingga hanya request pertama yang membuat mutation/audit. |

## Denial dan abuse matrix

| Case | Expected result |
| --- | --- |
| Guest | Redirect ke HR login. |
| Viewer/officer tanpa `offboardings.finalize` | `403 Forbidden`. |
| Unknown Offboarding ID | `404 Not Found`. |
| Archived Offboarding | Generic validation error, tanpa partial mutation. |
| Stale Employee atau Contract | Generic validation error, tanpa partial mutation. |
| Invalid target final status | Generic validation error, tanpa partial mutation. |
| Required task belum terminal | Generic validation error, tanpa partial mutation. |
| Payload mencoba override owner data | Diabaikan; locked server snapshot tetap source of truth. |

## Konsistensi Employees dan Contracts

- Employee hanya berubah saat finalization berhasil dan business date valid.
- Employee menjadi inactive dan memakai final employment status dari snapshot Offboarding.
- Contract aktif menjadi ended dengan `ended_at` sesuai exit date dan reason dari snapshot Offboarding.
- Jika audit owner module gagal, Employee, Contract, dan Offboarding kembali ke state awal.
- Jika Contract tidak ada, finalization tetap dapat menyelesaikan Employee dan Offboarding sesuai scope MVP.

## Batas downstream

- Tidak ada direct mutation ke Attendance.
- Tidak ada direct mutation ke Payroll.
- Tidak ada direct mutation ke Accounting.
- Tidak ada direct mutation ke Document Management.
- Integration event downstream tetap deferred sampai consumer contract disetujui.

## Route dan security review

- Semua mutation route `hr.offboardings.*` memiliki `auth`.
- Semua mutation route memiliki policy middleware sesuai action.
- Route finalization memakai `can:finalize,offboarding`.
- Error finalization untuk invariant internal dibuat generic agar tidak mengekspos PII, nama tabel, nomor kontrak, class internal, atau detail lock.
- Frontend visibility hanya UX; server tetap menjadi authorization boundary.

## Findings

Tidak ada blocker untuk Task 14.

Residual constraints yang sengaja belum masuk scope:

- Archive/restore Offboarding case belum tersedia.
- Due command untuk operasi harian belum tersedia.
- Filter operasional final belum lengkap.
- Integration event ke Attendance, Payroll, Accounting, dan DMS belum dibuka.
- Browser smoke test manual tetap direkomendasikan sebelum production pilot.

## Gate decision

Checkpoint D **PASS**. Task 14 - Filters, archive/restore, dan due command boleh dimulai dengan syarat:

- archive hanya untuk terminal case;
- restore tidak boleh menghidupkan kembali process aktif tanpa aturan eksplisit;
- due command read-only dan deterministic;
- filter harus paginated, bounded, dan tidak mengekspos data di luar permission.
