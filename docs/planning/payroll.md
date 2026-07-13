# Spec: Payroll

## Objective

Menghasilkan payroll run yang reproducible dari snapshot HR dan Attendance, melalui approval, payment, serta posting Accounting.

## Submodule dan urutan

1. `PayrollCalendars` dan `PayrollPeriods`.
2. `CompensationComponents` — earning, deduction, employer contribution.
3. `EmployeeCompensations` — assignment efektif-bertanggal.
4. `PayrollInputs` — snapshot HR/Attendance dan input manual auditable.
5. `PayrollRuns` dan `PayrollCalculations`.
6. `PayrollApprovals`, `Payslips`, dan `PayrollPayments`.
7. `PayrollPostings`, `PayrollReports`, dan `PayrollIntegrations`.

## Requirements

- Formula berversi, decimal-safe, effective-dated, dan hasil run dapat direproduksi.
- PII/pay amount dibatasi permission; payslip private.
- Approved run immutable; koreksi memakai reversal atau adjustment run.
- Accounting menerima balanced journal contract, bukan detail pribadi employee.

## Non-scope

Attendance capture, general ledger, tax filing vendor-specific, recruitment, dan bank transfer protocol spesifik sebelum adapter disetujui.

## Command design

`payroll:import-snapshots {period}`, `payroll:calculate {run}`, `payroll:validate {run}`, `payroll:generate-payslips {run}`, dan `payroll:post {run}` wajib idempotent serta menyediakan `--dry-run` untuk validasi/import/posting.

## Acceptance criteria dan test plan

- Golden tests formula, rounding, prorate, retroactive adjustment, duplicate input, approval denial, immutable run, balanced posting, signature/export, serta retry idempotency hijau.
- Input Attendance merujuk snapshot tertutup dari [Attendance](attendance.md); posting mengikuti [Accounting](accounting.md).

## Risks

Formula drift, floating-point, double payment/posting, data lintas periode, dan akses payroll berlebih.
