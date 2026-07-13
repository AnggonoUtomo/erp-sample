# Implementation Plan: Employee Contracts

## Overview

Implementasi dilakukan contract-first dan incremental. Setiap fase meninggalkan aplikasi dalam kondisi buildable. Vertical slice pertama hanya create + list draft contract; lifecycle lengkap ditambahkan setelah kontrak interval terbukti benar.

## Dependency graph

```txt
Employees + EmploymentTypes
          │
          ▼
Manifest + schema + interval rules
          │
          ▼
Create/list draft ──> activate ──> terminate/cancel/supersede
          │                              │
          ▼                              ▼
Audit + authorization              HR snapshot contract
          │                              │
          └──────────> UI + reports <────┘
```

## Architecture decisions

- Interval berlaku inklusif dan histori aktif tidak dioverwrite; lihat [ADR-001](decisions/001-effective-dated-contracts.md).
- Employee Contracts memiliki lifecycle sendiri dan tidak mengubah Employees diam-diam.
- Payroll integration ditunda sampai snapshot schema disetujui; model Eloquent tidak menjadi public contract.
- Tidak ada force delete pada rilis awal.

## Phase 1 — Contract and foundation

1. Scaffold formal module dan manifest dependency.
2. Tambahkan enum/state contract serta migration dasar.
3. Tulis overlap/effective-date tests dan service query.

### Checkpoint 1

- Module validator dan Pint hijau.
- Migration up/down tervalidasi.
- Boundary/open-ended/overlap tests hijau.

## Phase 2 — Vertical slice create and list

4. Tambahkan permission, policy, request, DTO, transaction, service, controller, dan route untuk create/list draft.
5. Tambahkan page Inertia typed: table, filters, pagination, form, detail preview.
6. Tambahkan authorization denial matrix, audit assertions, frontend tests, dan navigation.

### Checkpoint 2

- HR berizin dapat membuat lalu melihat draft end-to-end.
- Duplicate/range/required-end-date/overlap ditolak.
- Backend test, frontend test, lint, typecheck, dan build hijau.

## Phase 3 — Lifecycle

7. Implement activate dengan locking/idempotency.
8. Implement terminate dan cancel dengan reason.
9. Implement supersede sebagai satu atomic use case.

### Checkpoint 3

- State transition matrix dan concurrent activation teruji.
- Generic update tidak dapat mengubah kontrak aktif.
- Audit before/after serta actor tersedia.

## Phase 4 — Lifecycle data safety

10. Implement archive/restore tanpa force delete.
11. Tambahkan expiring filters/query dan command read-only.
12. Definisikan snapshot integration contract versi 1 tanpa menghubungkan Payroll lebih dahulu.

### Checkpoint final

- Seluruh acceptance criteria [specification](specification.md) hijau.
- Full backend/frontend quality gates hijau.
- Diff security/behavior review selesai dan docs diperbarui berdasarkan implementasi aktual.

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Race condition saat activate | Dua kontrak overlap | Transaction, lock employee/contract set, database-specific test |
| Open-ended contract memblokir renewal | Workflow buntu | Atomic supersede/terminate sebelum replacement |
| Backdated correction mengubah payroll lama | Rekonsiliasi finansial salah | Permission khusus di fase berikut dan snapshot immutable |
| Employment type diarsipkan | Label/history hilang | FK restrict/null policy diputuskan sebelum migration; snapshot code disimpan saat publish |
| UI lifecycle membypass policy | Unauthorized mutation | Policy server-side dan global denial matrix |
| Scope bocor ke file/DMS atau compensation | Module membesar | Pertahankan non-scope dan buat integration contract terpisah |

## Rollback strategy

- Setiap slice menjadi commit terpisah dan dapat direvert.
- Migration awal hanya additive dan memiliki `down()` yang teruji sebelum ada data production.
- Setelah data production ada, rollback code tidak boleh menjatuhkan tabel; disable module/navigation dan gunakan forward migration.
- Lifecycle event/integration belum dipublikasikan sebelum schema version disetujui.

