# ADR-002: Effective-Dated Termination Melalui Boundary Pemilik Domain

## Status

Accepted

## Date

2026-07-16

## Context

Finalisasi Offboarding perlu mengakhiri employment, tetapi:

- Employees adalah owner `active`, `ended_at`, dan `employment_status_id`;
- Employee Contracts adalah owner contract status/end date/reason;
- Attendance dan Payroll belum menjadi runtime consumer yang disetujui;
- direct import model/service internal akan membuat coupling dan transaction semantics sulit dijaga.

## Decision

Offboardings mengoordinasikan finalization melalui interface mutation resmi yang dimiliki dan diimplementasikan oleh Employees serta Employee Contracts.

Contract publik versi 1 yang disetujui:

- `EmployeeTerminationGateway` menerima employee identifier, expected state `ACTIVE`, target Employment Status final, effective date, reason, dan actor user identifier;
- `EmployeeContractTerminationGateway` menerima contract identifier, employee identifier, expected state `ACTIVE`, effective date, reason, dan actor user identifier;
- kedua contract mengembalikan result DTO minimal dan tidak mengekspos Eloquent model atau service internal;
- schema JSON dan manifest module menjadi contract machine-readable yang harus berubah versi bila bentuk payload berubah secara breaking.

Finalization:

1. hanya menerima case `READY_FOR_EXIT`;
2. memakai business date eksplisit dan menolak sebelum exit date;
3. mengunci aggregate dan owner records dengan urutan deterministic;
4. memvalidasi target Employment Status aktif dan final;
5. mengakhiri relevant active Contract pada exit date;
6. mengubah Employee menjadi inactive, mengisi `ended_at`, dan target final status;
7. mengubah Offboarding menjadi `COMPLETED`;
8. mencatat audit dalam transaction yang sama;
9. bersifat idempotent setelah sukses.

Tidak ada event Attendance/Payroll yang diterbitkan sampai consumer, schema, delivery, retry, dan ordering semantics disetujui.

## Alternatives considered

### Offboardings import model/service internal

Ditolak karena melanggar module boundary dan mudah menghasilkan coupling tersembunyi.

### HR melakukan perubahan manual pada tiga menu

Ditolak sebagai hasil final karena partial completion terlalu mudah terjadi.

### Event asynchronous untuk seluruh mutation

Ditolak untuk HR core state karena eventual consistency dan compensating action menambah risiko tanpa kebutuhan nyata.

### Database trigger

Ditolak karena business rule, permission, audit actor, dan error semantics menjadi tersembunyi.

## Consequences

### Positive

- Ownership domain tetap jelas.
- Atomicity dan idempotency dapat diuji.
- Adapter dapat berubah tanpa mengekspos model internal.
- Downstream integration dapat ditambahkan kemudian secara additive.

### Negative

- Owner modules perlu contract mutation baru.
- Transaction lintas module HR memerlukan lock order yang konsisten.
- Finalization belum dapat dibangun pada vertical slice pertama.

## Security and failure constraints

- Authorization diperiksa sebelum transaction dan invariant diperiksa ulang di dalam lock.
- Identifier/stale-state mismatch fail-closed.
- Error response tidak membawa PII atau internal lock/state detail.
- Audit mencatat bahwa reason tersedia tanpa menyalin reason sensitif secara penuh.
- Partial failure me-rollback seluruh mutation.
- Retry setelah commit tidak membuat audit/event kedua.

## Task 11 implementation note

Boundary owner module tersedia sejak 2026-07-16. Implementasi memakai transaction dan row lock, melakukan stale-state validation setelah lock, serta menggunakan actor eksplisit tanpa fallback ke authenticated user. Belum ada route finalization dan belum ada event Attendance/Payroll; orchestration lintas owner module tetap ditunda ke Task 12.

## Task 12 implementation note

Orchestration finalization tersedia sejak 2026-07-16. Request hanya menerima business date; employee, contract, target status, effective date, dan reason dibaca dari snapshot Offboarding yang dikunci. Lock order adalah Offboarding, owner Employee mutation, owner Contract mutation, lalu task snapshot. Status `COMPLETED`, actor, timestamp, business date, owner mutations, dan audit berada dalam satu transaction. Request ulang setelah commit adalah no-op dan tidak membuat audit tambahan. Event Attendance/Payroll tetap tidak diterbitkan.
