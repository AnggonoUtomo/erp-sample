# Spec: Attendance

## Objective

Mencatat waktu kerja secara auditable dari jadwal sampai penutupan periode, dengan HR sebagai sumber employee dan work location.

Attendance wajib memakai [HR Integration Contracts consumer handoff](../projects/hr/integration-contracts/consumer-handoff.md) untuk membaca employee eligibility dan assignment. Jangan query table/model internal HR secara bebas untuk flow schedule, check-in/out, overtime eligibility, atau period closing.

## Submodule dan urutan

1. `AttendancePolicies` — toleransi, rounding, lateness, dan eligibility.
2. `Shifts` — template jam kerja dan break.
3. `WorkSchedules` — assignment employee/kelompok per rentang tanggal.
4. `Attendances` — check-in/out, sumber perangkat, geofence, dan koreksi.
5. `LeaveTypes` dan `LeaveRequests` — hak, request, approval, cancellation.
6. `Overtimes` — request, approval, actual duration, payroll eligibility.
7. `AttendancePeriods` — lock, exception resolution, dan immutable payroll snapshot.
8. `AttendanceReports` dan `AttendanceIntegrations`.

## Requirements

- Simpan timezone dan timestamp asli; hitung hari kerja secara eksplisit.
- Tolak presensi employee yang tidak eligible berdasarkan HR snapshot.
- Baca `EmployeeSnapshotV1` dan `EmployeeAssignmentSnapshotV1` melalui provider resmi HR Integration Contracts.
- Koreksi tidak menimpa bukti asli dan wajib menyimpan actor/reason.
- Closing menghasilkan snapshot berversi untuk Payroll.

## Non-scope

Perhitungan gaji, master employee, payroll journal, biometrik vendor-specific, dan document storage engine.

## Command design

`attendance:generate-schedules`, `attendance:flag-anomalies`, `attendance:close-period {period}`, dan `attendance:export-snapshot {period}` harus idempotent, mendukung `--dry-run`, dan gagal tertutup saat contract HR tidak valid.

Sebelum implementasi command Attendance, jalankan:

```bash
php artisan hr:integration-contracts:validate
php artisan hr:integration-contracts:describe
```

## Acceptance criteria dan test plan

- Shift lintas tengah malam, timezone, DST, geofence, duplicate punch, missed punch, leave overlap, overtime overlap, denial matrix, lock period, dan retry command teruji.
- Snapshot tertutup tidak berubah ketika profil HR berubah.
- Jalankan commands standar pada [index planning](README.md).

## Risks

Clock device tidak terpercaya, perubahan schedule retroaktif, hierarchy approval circular, dan perbedaan timezone. Payroll hanya boleh membaca snapshot: [Payroll](payroll.md).
