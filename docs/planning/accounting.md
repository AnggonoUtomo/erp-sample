# Spec: Accounting

## Objective

Menyediakan general ledger auditable dengan chart of accounts, journal, period close, receivable/payable foundation, dan laporan keuangan.

## Submodule dan urutan

1. `FiscalCalendars`, `ChartOfAccounts`, `AccountingDimensions`.
2. `Journals` dan `LedgerPostings`.
3. `AccountingPeriods` — open/close/reopen terkontrol.
4. `AccountsReceivable` dan `AccountsPayable`.
5. `CashBanks`, `Reconciliations`, `FixedAssets`.
6. `FinancialReports` dan `AccountingIntegrations`.

## Requirements

- Debit selalu sama dengan credit; posted journal immutable.
- Reversal mempertahankan linkage ke journal asal.
- Nomor dokumen unik per company/fiscal period dan posting idempotent.
- Payroll integration hanya menerima journal contract dari [Payroll](payroll.md).

## Non-scope

Payroll calculation, CRM pipeline, tax engine negara tertentu, inventory costing, dan consolidation multi-company pada slice awal.

## Command design

`accounting:validate-journal`, `accounting:post {journal}`, `accounting:close-period {period}`, `accounting:trial-balance {period}`, semua mutating command mendukung `--dry-run` kecuali post final yang memakai confirmation/token internal.

## Acceptance criteria dan test plan

- Unbalanced journal ditolak; duplicate external reference idempotent; closed-period mutation ditolak; reversal, concurrency, permissions, audit, trial balance, dan integration contract teruji.

## Risks

Double posting, period leakage, precision/rounding, mutable ledger, serta coupling ke format internal Payroll.
