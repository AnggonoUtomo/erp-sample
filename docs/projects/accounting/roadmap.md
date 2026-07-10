# Roadmap Project Accounting

Roadmap ini adalah rencana awal untuk membangun project `Accounting` di atas starterkit modular. Fokusnya adalah membuat fondasi akuntansi yang rapi, bisa diaudit, dan siap dihubungkan dengan project lain seperti inventory, purchasing, sales, payroll, atau operations.

## Prinsip Utama

- Semua transaksi finansial harus punya jejak audit.
- Data master harus stabil sebelum transaksi dibuat.
- Posting jurnal harus atomic dan balance.
- Laporan tidak mengubah data transaksi.
- Integrasi antar project memakai event/contract, bukan akses internal sembarangan.

## Struktur Project Awal

```txt
app/Modules/Accounting/
  ChartOfAccounts/
  AccountingPeriods/
  Journals/
  GeneralLedgers/
  Reports/
  TaxSettings/
  OpeningBalances/
  CostCenters/
  Currencies/
```

Frontend:

```txt
resources/js/pages/accounting/
  chart-of-accounts/
  accounting-periods/
  journals/
  general-ledgers/
  reports/
  tax-settings/
  opening-balances/
  cost-centers/
  currencies/
```

## Phase 1: Foundation

Target: struktur dasar accounting siap dipakai.

Module:

- `Currencies`
- `ChartOfAccounts`
- `AccountingPeriods`
- `CostCenters`
- `TaxSettings`

Fitur:

- Multi currency dasar.
- Chart of account dengan hierarchy.
- Account type: Asset, Liability, Equity, Revenue, Expense.
- Account normal balance: Debit/Credit.
- Accounting period: open, locked, closed.
- Cost center untuk segmentasi biaya.
- Tax setting dasar.

Validasi penting:

- Account code unik.
- Parent account tidak boleh menjadi child dari dirinya sendiri.
- Account yang sudah dipakai transaksi tidak boleh dihapus.
- Period yang closed tidak boleh menerima jurnal baru.

## Phase 2: Journal Core

Target: pencatatan transaksi manual dan posting jurnal.

Module:

- `Journals`
- `JournalApprovals`
- `GeneralLedgers`

Fitur:

- Draft journal.
- Submit journal.
- Approve/reject journal.
- Post journal ke general ledger.
- Reverse journal.
- Attach supporting document dengan media library.
- Auto numbering journal.

Validasi penting:

- Total debit harus sama dengan total credit.
- Journal date harus berada di period yang open.
- Account harus aktif.
- Posted journal tidak bisa diedit langsung.
- Reversal harus membuat jurnal pembalik, bukan menghapus jurnal asli.

Event awal:

- `JournalSubmitted`
- `JournalApproved`
- `JournalPosted`
- `JournalReversed`

## Phase 3: Reporting

Target: laporan keuangan awal bisa dibaca.

Module:

- `Reports`
- `TrialBalances`
- `FinancialStatements`

Fitur:

- Trial balance.
- General ledger detail.
- Profit and loss.
- Balance sheet.
- Cash flow sederhana.
- Export PDF/Excel.
- Filter period, date range, cost center, dan currency.

Catatan:

- Laporan membaca ledger dan snapshot, bukan menghitung ulang dari UI.
- Untuk data besar, export masuk queue.

## Phase 4: Operational Accounting

Target: transaksi operasional mulai terhubung ke accounting.

Module kandidat:

- `Receivables`
- `Payables`
- `Payments`
- `BankAccounts`
- `BankReconciliations`

Fitur:

- Customer invoice.
- Vendor bill.
- Payment receipt.
- Payment disbursement.
- Bank account master.
- Bank reconciliation.
- Aging receivable/payable.

Integrasi event:

- Sales project dispatch `InvoiceIssued`.
- Purchasing project dispatch `BillApproved`.
- Accounting listen event dan membuat draft journal.

## Phase 5: Controls & Closing

Target: accounting aman untuk proses akhir bulan.

Module:

- `ClosingPeriods`
- `AuditTrails`
- `PostingRules`

Fitur:

- Lock period.
- Month-end closing checklist.
- Posting rule per source document.
- Recurring journal.
- Accrual journal.
- Depreciation placeholder.
- Audit trail per transaksi finansial.

Validasi penting:

- Hanya role tertentu bisa close/reopen period.
- Reopen period harus punya alasan.
- Semua perubahan setelah approval harus tercatat.

## Phase 6: Advanced

Target: siap untuk kebutuhan bisnis yang lebih besar.

Module kandidat:

- `Budgets`
- `FixedAssets`
- `MultiCompany`
- `Consolidations`
- `ExchangeRates`

Fitur:

- Budgeting per account/cost center.
- Fixed asset register.
- Depreciation schedule.
- Multi-company accounting.
- Currency revaluation.
- Consolidated financial report.

## Permission Awal

Contoh permission:

```txt
accounting.view
accounting.dashboard.view
chart-of-accounts.view
chart-of-accounts.create
chart-of-accounts.update
chart-of-accounts.delete
accounting-periods.view
accounting-periods.update
journals.view
journals.create
journals.update
journals.approve
journals.post
journals.reverse
general-ledgers.view
reports.view
reports.export
```

Role awal:

- `finance-admin`: akses penuh accounting.
- `accountant`: create/update journal dan lihat laporan.
- `finance-manager`: approve/post/reverse dan close period.
- `auditor`: read-only semua laporan dan audit trail.

## UI/UX Arah Awal

Accounting sebaiknya terasa padat, rapi, dan mudah discan:

- Header ringkas dengan action utama.
- Table kuat dengan filter, sort, pagination, dan bulk action.
- Detail panel kanan untuk preview jurnal/account.
- Status badge jelas: Draft, Submitted, Approved, Posted, Reversed.
- Form transaksi memakai grid dan line-item table.
- Shortcut keyboard untuk create, save draft, submit, search, dan fokus table.

## Urutan Implementasi Yang Disarankan

1. `php artisan make:module Currencies --project=Accounting`
2. `php artisan make:module ChartOfAccounts --project=Accounting`
3. `php artisan make:module AccountingPeriods --project=Accounting`
4. `php artisan make:module CostCenters --project=Accounting`
5. `php artisan make:module Journals --project=Accounting`
6. `php artisan make:module GeneralLedgers --project=Accounting`
7. `php artisan make:module Reports --project=Accounting`

## Definition of Done Per Module

- Route, permission, navigation, provider tersedia.
- Policy terpasang.
- FormRequest tersedia untuk aksi mutasi.
- DTO dipakai untuk input service.
- Service berisi use case.
- Transaction membungkus write operation.
- Test route dan permission tersedia.
- Test operasi utama tersedia.
- Audit log untuk aksi penting tersedia.
- UI sudah dipisah menjadi komponen.
- Empty state, loading state, dan error state tersedia.

## Risiko Yang Perlu Dijaga

- Journal tidak balance.
- Period closed masih bisa menerima transaksi.
- Data ledger berubah tanpa audit.
- Report tidak konsisten karena menghitung dari sumber berbeda.
- Permission terlalu longgar untuk approval/posting.
- Integrasi antar project terlalu rapat dan sulit diubah.
