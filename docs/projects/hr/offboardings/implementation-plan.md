# Implementation Plan: HR Offboardings

## Overview

Implementasi dilakukan contract-first dan vertical. Foundation hanya mendefinisikan state serta boundary. Slice berikut membuktikan template dan draft snapshot. Effective finalization baru dibuka setelah kontrak mutation Employees/Employee Contracts disetujui dan diuji.

## Dependency graph

```txt
Employees + EmploymentStatuses + optional EmployeeContracts + Console Users
                                  |
                                  v
                 Module + state + permission contract
                                  |
                                  v
                     Template + ordered items
                                  |
                                  v
                Draft + immutable task snapshot
                                  |
                     +------------+------------+
                     v                         v
                Task lifecycle           Read/progress
                     +------------+------------+
                                  v
                           READY_FOR_EXIT
                                  |
                                  v
            Employees/Contracts mutation interfaces
                                  |
                                  v
                   Atomic effective finalization
                                  |
                                  v
              Query/UI/command + deferred events
```

## Architecture decisions

- Checklist menjadi snapshot; lihat [ADR-001](decisions/001-checklist-snapshot-and-exit-readiness.md).
- Readiness dan employment termination adalah state berbeda.
- Employee dan Contract hanya dimutasi pemilik domain melalui contract resmi; lihat [ADR-002](decisions/002-effective-dated-termination-boundary.md).
- File evidence tetap memakai Employee Documents/DMS.
- Downstream event tidak dipublikasikan secara spekulatif.

## Phase 1 — Foundation dan template

1. Module manifest, permissions, state enums, provider, serta foundation test.
2. Template checklist create/list.
3. Archive/restore template dan used-template protection.

### Checkpoint A

- Module validator hijau.
- Tidak ada runtime route/navigation sebelum policy-backed slice tersedia.
- Template deterministic dan tidak hard-delete.

## Phase 2 — Draft snapshot

4. Draft offboarding dan task snapshot atomic.
5. Duplicate active/idempotency guard.
6. Detail dan progress read model.

### Checkpoint B

- Edit template tidak mengubah case existing.
- Failure menyalin task me-rollback case.
- Exit date/reason/status target dan employment context konsisten.

## Phase 3 — Operational lifecycle

7. Activate offboarding.
8. Assignment dan task completion.
9. Controlled skip/reopen serta ready transition.
10. Cancel lifecycle.

### Checkpoint C

- Invalid/repeated transition fail-closed.
- Required incomplete memblokir readiness.
- Reopen task dari ready mengembalikan case ke in-progress.

## Phase 4 — Effective exit

11. Formalisasi mutation interface Employees dan Employee Contracts.
12. Atomic finalization pada/ setelah exit date.
13. Rollback, retry, concurrency, dan audit matrix.

### Checkpoint D

- Employee, Contract, dan Offboarding berubah bersama atau tidak sama sekali.
- Finalize retry idempotent.
- Tidak ada Attendance/Payroll side effect langsung.

## Phase 5 — Operational readiness

14. Filters, archive/restore, dan due command.
15. Frontend completion, accessibility, responsive behavior.
16. Evaluasi integration event hanya jika consumer nyata tersedia. Hasil 2026-07-17: deferred; lihat [ADR-004](decisions/004-defer-integration-event-v1.md).

### Final checkpoint

- Acceptance criteria dan full quality gates hijau.
- Authorization/security review lulus.
- Docs dan HR roadmap sesuai behavior aktual.

## Risks and mitigations

| Risk                                           | Impact                         | Mitigation                                    |
| ---------------------------------------------- | ------------------------------ | --------------------------------------------- |
| Checklist ready dianggap employment terminated | User berhenti terlalu cepat    | State `READY_FOR_EXIT` terpisah               |
| Template berubah                               | Histori tidak terpercaya       | Snapshot task                                 |
| Duplicate active case                          | Dua owner proses               | Identity key + lock + unique guard            |
| Finalization parsial                           | Employee/Contract bertentangan | Satu transaction + adapter + lock order       |
| Final status ambigu                            | Salah status                   | Validasi `is_final_status`, pilihan eksplisit |
| Contract sudah berubah                         | Exit menimpa state baru        | Snapshot/check ulang contract saat finalize   |
| Exit future diterapkan dini                    | Payroll/attendance salah       | Explicit business date guard                  |
| Integration scope melebar                      | Delivery lambat                | Events/downstream deferred                    |

## Rollback strategy

- Setiap task menjadi commit independently revertable.
- Migration additive memiliki `down()` sebelum production data.
- Setelah data production ada, rollback memakai forward migration dan disable navigation; histori tidak di-drop.
- Finalization failure selalu rollback transaction.
- Public event tidak diterbitkan sebelum schema dan consumer disetujui.
