# Roadmap Project Attendance

Roadmap ini adalah rencana awal untuk membangun project `Attendance` di atas starterkit modular. Fokusnya adalah mengelola presensi, jadwal kerja, shift, izin, lembur, dan rekap kehadiran yang nanti bisa menjadi sumber data untuk project `Payroll`.

## Prinsip Utama

- Attendance menjadi sumber kebenaran untuk jam hadir, pulang, telat, izin, dan lembur.
- Semua perubahan data presensi harus punya audit trail.
- Jadwal kerja harus jelas sebelum presensi dihitung.
- Koreksi presensi harus melalui approval.
- Integrasi ke payroll memakai event/contract, bukan akses internal sembarangan.

## Struktur Project Awal

```txt
app/Modules/Attendance/
  Employees/
  WorkSchedules/
  Shifts/
  Attendances/
  LeaveRequests/
  Overtimes/
  AttendanceCorrections/
  Holidays/
  AttendanceReports/
  Devices/
```

Frontend:

```txt
resources/js/pages/attendance/
  employees/
  work-schedules/
  shifts/
  attendances/
  leave-requests/
  overtimes/
  attendance-corrections/
  holidays/
  attendance-reports/
  devices/
```

## Phase 1: Foundation

Target: master data attendance siap dipakai.

Module:

- `Employees`
- `WorkSchedules`
- `Shifts`
- `Holidays`
- `Devices`

Fitur:

- Employee profile ringkas untuk attendance.
- Work schedule per employee/team.
- Shift master: start, end, break, grace period.
- Holiday calendar.
- Device/source master: web, mobile, biometric, import.
- Assignment schedule.

Validasi penting:

- Employee wajib terhubung ke user jika butuh self-service.
- Shift end lebih kecil dari start harus dianggap overnight shift.
- Jadwal tidak boleh overlap untuk employee yang sama.
- Holiday tidak boleh duplicate pada tanggal dan calendar yang sama.

## Phase 2: Clock In/Out Core

Target: presensi harian bisa dicatat.

Module:

- `Attendances`
- `AttendanceLogs`
- `AttendanceStatuses`

Fitur:

- Clock in.
- Clock out.
- Break in/out optional.
- Capture source presensi.
- Geolocation optional.
- Photo proof optional dengan media library.
- Auto status: present, late, absent, half-day.
- Daily attendance summary.

Validasi penting:

- Employee tidak boleh clock in dua kali tanpa rule khusus.
- Clock out tidak boleh sebelum clock in.
- Presensi di luar schedule harus ditandai exception.
- Semua raw log harus disimpan, meski summary berubah.

Event awal:

- `EmployeeClockedIn`
- `EmployeeClockedOut`
- `AttendanceMarkedLate`
- `AttendanceSummaryCalculated`

## Phase 3: Leave & Overtime

Target: izin dan lembur bisa diminta dan disetujui.

Module:

- `LeaveRequests`
- `LeaveTypes`
- `Overtimes`
- `ApprovalFlows`

Fitur:

- Leave request.
- Leave type master.
- Leave balance placeholder.
- Overtime request.
- Approval/reject dengan catatan.
- Attach document untuk izin/sakit.
- Auto update attendance status setelah approval.

Validasi penting:

- Leave date tidak boleh bentrok dengan approved leave lain.
- Overtime harus punya tanggal, durasi, alasan, dan approver.
- Approved leave/overtime tidak bisa diedit langsung.
- Rejection wajib punya alasan.

## Phase 4: Corrections & Exceptions

Target: data presensi bisa dikoreksi dengan kontrol.

Module:

- `AttendanceCorrections`
- `Exceptions`
- `ApprovalHistories`

Fitur:

- Request correction untuk lupa clock in/out.
- Manager correction.
- Exception list: missing clock out, late, early leave, absent.
- Approval history.
- Recalculate attendance summary setelah correction approved.

Validasi penting:

- Correction tidak boleh mengubah raw log asli.
- Correction harus membuat adjustment record.
- Period yang sudah locked payroll tidak boleh dikoreksi tanpa permission khusus.

## Phase 5: Reports & Export

Target: HR bisa membaca dan mengekspor rekap.

Module:

- `AttendanceReports`
- `Timesheets`
- `AttendanceExports`

Fitur:

- Daily report.
- Monthly timesheet.
- Late report.
- Absent report.
- Overtime report.
- Leave report.
- Export Excel/PDF.
- Filter departement, employee, date range, status.

Catatan:

- Export besar masuk queue.
- Report payroll sebaiknya memakai snapshot attendance period.

## Phase 6: Payroll Integration

Target: attendance menjadi input payroll.

Module kandidat:

- `AttendancePeriods`
- `PayrollSnapshots`
- `AttendanceLocks`

Fitur:

- Attendance period.
- Lock attendance period.
- Generate payroll attendance snapshot.
- Push event ke Payroll.
- Reopen period dengan alasan.

Integrasi event:

- Attendance dispatch `AttendancePeriodClosed`.
- Attendance dispatch `PayrollAttendanceSnapshotGenerated`.
- Payroll listen event untuk mengambil summary yang sudah locked.

## Permission Awal

Contoh permission:

```txt
attendance.view
attendance.dashboard.view
employees.view
employees.create
employees.update
work-schedules.view
work-schedules.manage
shifts.view
shifts.manage
attendances.view
attendances.clock
attendances.correct
attendance-corrections.approve
leave-requests.view
leave-requests.create
leave-requests.approve
overtimes.view
overtimes.create
overtimes.approve
attendance-reports.view
attendance-reports.export
attendance-periods.lock
attendance-periods.reopen
```

Role awal:

- `attendance-admin`: akses penuh attendance.
- `hr-manager`: manage schedule, approval, report, lock period.
- `supervisor`: approve leave/overtime/correction timnya.
- `employee`: clock in/out dan request leave/overtime/correction.
- `attendance-viewer`: read-only report sesuai akses.

## UI/UX Arah Awal

Attendance sebaiknya terasa operasional, cepat, dan jelas:

- Dashboard hari ini: present, late, absent, leave, overtime.
- Clock card besar untuk user employee.
- Table attendance dengan filter tanggal/status.
- Calendar view untuk leave/holiday/schedule.
- Detail panel kanan untuk log harian.
- Badge status: Present, Late, Absent, Leave, Overtime, Exception.
- Shortcut keyboard untuk search, fokus table, open correction, approve.

## Urutan Implementasi Yang Disarankan

1. `php artisan make:module Employees --project=Attendance`
2. `php artisan make:module Shifts --project=Attendance`
3. `php artisan make:module WorkSchedules --project=Attendance`
4. `php artisan make:module Holidays --project=Attendance`
5. `php artisan make:module Attendances --project=Attendance`
6. `php artisan make:module LeaveRequests --project=Attendance`
7. `php artisan make:module Overtimes --project=Attendance`
8. `php artisan make:module AttendanceCorrections --project=Attendance`
9. `php artisan make:module AttendanceReports --project=Attendance`

## Definition of Done Per Module

- Route, permission, navigation, provider tersedia.
- Policy terpasang.
- FormRequest tersedia untuk aksi mutasi.
- DTO dipakai untuk input service.
- Service berisi use case.
- Transaction membungkus write operation.
- Test route dan permission tersedia.
- Test operasi utama tersedia.
- Audit log untuk clock, correction, approval, dan lock period tersedia.
- Event penting tersedia untuk payroll.
- UI sudah dipisah menjadi komponen.
- Empty state, loading state, dan error state tersedia.

## Risiko Yang Perlu Dijaga

- Raw attendance log berubah tanpa audit.
- Shift overnight salah dihitung.
- Correction mengubah data yang sudah masuk payroll.
- Employee bisa melihat data employee lain tanpa permission yang benar.
- Export report berat membuat request timeout.
- Payroll membaca attendance yang belum locked.
