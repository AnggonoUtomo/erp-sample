# ADR-002: Versioned Employee Contract Snapshot Boundary

## Status

Accepted — 2026-07-13.

## Context

Payroll dan project lain memerlukan fakta kontrak yang berlaku pada business date tertentu. Memberikan akses langsung ke model atau tabel `hr_employee_contracts` akan mengikat consumer pada schema internal, lifecycle HR, soft-delete scope, serta field internal seperti notes. Snapshot juga harus dapat direproduksi walaupun profile employee saat ini berubah.

## Decision

Employee Contracts menerbitkan boundary read-only `EmployeeContractSnapshotReader`. Implementasi v1 menerima `employeeId`, `effectiveDate`, dan `capturedAt` eksplisit, lalu mengembalikan `EmployeeContractSnapshotV1` atau `null`.

Payload v1 memiliki tepat tujuh field:

```json
{
  "schemaVersion": 1,
  "employeeId": 123,
  "contractId": 456,
  "employmentTypeCode": "FIXED_TERM",
  "validFrom": "2026-01-01",
  "validUntil": "2026-12-31",
  "capturedAt": "2026-07-13T09:30:00Z"
}
```

Schema normatif berada di `Integration/Schemas/employee-contract-snapshot-v1.json`. `capturedAt` diberikan caller, bukan dibuat diam-diam oleh projector, agar pemanggilan dengan input sama menghasilkan payload sama. Projector hanya memilih contract `ACTIVE` atau `ENDED` yang efektif secara inklusif; draft, cancelled, dan archived tidak diterbitkan.

## Consequences

- Consumer bergantung pada reader interface dan schema version, bukan Eloquent model HR.
- Notes, nama/profile employee, attachment, compensation, dan data identitas tidak dapat bocor melalui v1.
- Perubahan breaking memerlukan schema version baru; v1 tidak diubah secara destruktif.
- Task ini belum memasang listener, scheduler, transport event, atau implementasi Payroll. Consumer integration dikerjakan terpisah setelah contract disetujui.

## Alternatives rejected

- Direct model/table access ditolak karena coupling dan bypass terhadap lifecycle scope.
- Menghasilkan `capturedAt` dari waktu runtime di dalam projector ditolak karena menghilangkan reproducibility.
- Memasukkan employee profile ditolak karena bukan fakta kontrak historis dan memperbesar exposure data.

## Verification

```bash
php artisan test --filter=EmployeeContractSnapshot
php artisan module:validate
```

Contract test mengunci exact field set, schema v1, effective-date semantics, exclusion lifecycle, dan reproducibility setelah current profile berubah.
