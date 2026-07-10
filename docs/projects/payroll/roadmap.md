# Roadmap Project Payroll

Roadmap ini adalah rencana awal untuk membangun project `Payroll` di atas starterkit modular. Fokusnya adalah mengelola komponen gaji, payroll period, perhitungan payroll, payslip, approval, dan integrasi ke accounting serta attendance.

## Prinsip Utama

- Payroll harus memakai data yang sudah locked atau snapshot.
- Perhitungan payroll harus bisa ditelusuri komponennya.
- Perubahan gaji dan komponen harus punya audit trail.
- Approval payroll wajib sebelum publish payslip.
- Integrasi ke accounting memakai journal/event, bukan akses internal langsung.

## Struktur Project Awal

```txt
app/Modules/Payroll/
  Employees/
  SalaryComponents/
  SalaryStructures/
  PayrollPeriods/
  PayrollRuns/
  Payslips/
  Deductions/
  Benefits/
  TaxRules/
  PayrollReports/
```

Frontend:

```txt
resources/js/pages/payroll/
  employees/
  salary-components/
  salary-structures/
  payroll-periods/
  payroll-runs/
  payslips/
  deductions/
  benefits/
  tax-rules/
  payroll-reports/
```

## Phase 1: Foundation

Target: master data payroll siap dipakai.

Module:

- `Employees`
- `SalaryComponents`
- `SalaryStructures`
- `Benefits`
- `Deductions`
- `TaxRules`

Fitur:

- Employee payroll profile.
- Salary component: earning, deduction, benefit, tax.
- Salary structure per employee/grade.
- Fixed allowance/deduction.
- Benefit master.
- Tax rule placeholder.
- Bank account payroll.

Validasi penting:

- Employee payroll profile wajib punya effective date.
- Salary component code unik.
- Component type menentukan apakah menambah atau mengurangi net pay.
- Salary structure yang sudah dipakai payroll tidak boleh dihapus langsung.

## Phase 2: Payroll Period & Snapshot

Target: payroll memakai data periode yang stabil.

Module:

- `PayrollPeriods`
- `PayrollSnapshots`
- `AttendanceImports`

Fitur:

- Payroll period: draft, processing, approved, paid, closed.
- Import attendance snapshot dari project Attendance.
- Snapshot salary structure.
- Snapshot benefit/deduction.
- Lock payroll input.
- Reopen period dengan alasan.

Validasi penting:

- Payroll period tidak boleh overlap.
- Payroll run hanya boleh dari period yang inputnya locked.
- Attendance snapshot harus berasal dari attendance period yang closed.
- Reopen period harus punya permission dan alasan.

Event integrasi:

- Listen `PayrollAttendanceSnapshotGenerated` dari Attendance.
- Dispatch `PayrollPeriodInputLocked`.

## Phase 3: Payroll Calculation

Target: payroll bisa dihitung, direview, dan disetujui.

Module:

- `PayrollRuns`
- `PayrollRunItems`
- `PayrollApprovals`

Fitur:

- Generate payroll run.
- Calculation detail per employee.
- Gross pay, deduction, tax, net pay.
- Manual adjustment dengan alasan.
- Recalculate draft payroll.
- Approval/reject payroll run.
- Payroll variance highlight.

Validasi penting:

- Approved payroll tidak bisa recalculated.
- Manual adjustment wajib punya alasan.
- Employee tidak boleh muncul dua kali dalam payroll run yang sama.
- Net pay negatif harus ditandai exception dan butuh approval khusus.

Event awal:

- `PayrollRunGenerated`
- `PayrollRunApproved`
- `PayrollRunRejected`
- `PayrollRunClosed`

## Phase 4: Payslip & Payment

Target: payslip bisa dipublish dan pembayaran bisa dipantau.

Module:

- `Payslips`
- `PayrollPayments`
- `BankDisbursements`

Fitur:

- Generate payslip.
- Publish payslip ke employee.
- Download payslip PDF.
- Payment status: pending, processed, paid, failed.
- Bank disbursement export.
- Employee self-service payslip.

Validasi penting:

- Payslip hanya bisa publish dari approved payroll.
- Payslip yang sudah publish harus versioned jika ada correction.
- Payment status paid tidak boleh dibatalkan tanpa reversal.

## Phase 5: Accounting Integration

Target: payroll bisa menghasilkan jurnal accounting.

Module:

- `PayrollJournals`
- `PostingRules`
- `PayrollAccruals`

Fitur:

- Payroll posting rule.
- Generate journal draft ke Accounting.
- Payroll expense accrual.
- Tax payable/payroll liability mapping.
- Reversal journal untuk correction.

Integrasi event:

- Payroll dispatch `PayrollRunApproved`.
- Accounting listen event dan membuat draft journal payroll.
- Accounting dispatch `JournalPosted` untuk update status posting payroll.

## Phase 6: Reports & Compliance

Target: HR/Finance bisa membaca laporan payroll.

Module:

- `PayrollReports`
- `TaxReports`
- `ComplianceExports`

Fitur:

- Payroll summary.
- Employee payroll history.
- Component report.
- Tax report placeholder.
- Bank payment report.
- Export PDF/Excel.
- Audit report payroll adjustment.

Catatan:

- Export besar masuk queue.
- Laporan compliance harus membaca snapshot final, bukan data master terbaru.

## Phase 7: Advanced

Target: payroll siap untuk kebutuhan bisnis yang lebih kompleks.

Module kandidat:

- `Loans`
- `Reimbursements`
- `MultiCompanyPayroll`
- `Prorations`
- `FormulaEngine`

Fitur:

- Employee loan.
- Reimbursement claim.
- Multi-company payroll.
- Proration join/resign/mid-period changes.
- Formula component.
- Retroactive payroll adjustment.

## Permission Awal

Contoh permission:

```txt
payroll.view
payroll.dashboard.view
payroll-employees.view
payroll-employees.update
salary-components.view
salary-components.manage
salary-structures.view
salary-structures.manage
payroll-periods.view
payroll-periods.manage
payroll-periods.lock
payroll-periods.reopen
payroll-runs.view
payroll-runs.generate
payroll-runs.recalculate
payroll-runs.approve
payroll-runs.close
payslips.view
payslips.publish
payslips.download
payroll-reports.view
payroll-reports.export
payroll-journals.generate
```

Role awal:

- `payroll-admin`: akses penuh payroll.
- `payroll-officer`: manage input, generate payroll, payslip.
- `payroll-manager`: approve payroll dan close period.
- `finance-manager`: view payroll journal dan payment report.
- `employee`: view/download payslip miliknya.
- `payroll-auditor`: read-only semua payroll dan audit trail.

## UI/UX Arah Awal

Payroll sebaiknya terasa tenang, terkontrol, dan kuat untuk review:

- Dashboard period aktif dan payroll status.
- Stepper workflow: Input, Calculate, Review, Approve, Publish, Pay, Close.
- Table payroll run dengan expandable component detail.
- Exception panel untuk net pay negatif, missing bank, missing attendance.
- Detail panel kanan untuk employee payroll breakdown.
- Badge status: Draft, Processing, Reviewed, Approved, Published, Paid, Closed.
- Shortcut keyboard untuk search, focus exceptions, approve, export.

## Urutan Implementasi Yang Disarankan

1. `php artisan make:module Employees --project=Payroll`
2. `php artisan make:module SalaryComponents --project=Payroll`
3. `php artisan make:module SalaryStructures --project=Payroll`
4. `php artisan make:module PayrollPeriods --project=Payroll`
5. `php artisan make:module PayrollRuns --project=Payroll`
6. `php artisan make:module Payslips --project=Payroll`
7. `php artisan make:module PayrollPayments --project=Payroll`
8. `php artisan make:module PayrollJournals --project=Payroll`
9. `php artisan make:module PayrollReports --project=Payroll`

## Definition of Done Per Module

- Route, permission, navigation, provider tersedia.
- Policy terpasang.
- FormRequest tersedia untuk aksi mutasi.
- DTO dipakai untuk input service.
- Service berisi use case.
- Transaction membungkus write operation.
- Test route dan permission tersedia.
- Test operasi utama tersedia.
- Audit log untuk calculation, adjustment, approval, publish, dan payment tersedia.
- Snapshot dipakai untuk perhitungan final.
- Event penting tersedia untuk attendance dan accounting.
- UI sudah dipisah menjadi komponen.
- Empty state, loading state, dan error state tersedia.

## Risiko Yang Perlu Dijaga

- Payroll dihitung dari data master yang berubah setelah period berjalan.
- Attendance belum locked tapi sudah dipakai payroll.
- Adjustment payroll tidak punya alasan dan audit.
- Payslip berubah setelah publish tanpa versioning.
- Permission payroll terlalu longgar karena data sangat sensitif.
- Journal accounting tidak balance atau tidak sinkron dengan payroll approved.
