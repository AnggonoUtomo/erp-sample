# Implementation Plan: HR Onboardings

## Overview

Implementasi dilakukan contract-first dan vertical. Slice pertama membuktikan template dapat disalin menjadi draft onboarding dengan task snapshot; lifecycle dan UI lengkap ditambahkan setelah invariant tersebut stabil.

## Dependency graph

```txt
Employees + optional EmployeeContracts + Console Users
                         |
                         v
        Module contract + state + schema
                         |
                         v
       Template + ordered template items
                         |
                         v
     Draft onboarding + immutable task snapshot
                         |
             +-----------+-----------+
             v                       v
      Task lifecycle          Onboarding lifecycle
             +-----------+-----------+
                         v
          Query/UI + overdue command
                         |
                         v
          Integration contract (later)
```

## Architecture decisions

- Template item disalin menjadi task snapshot; lihat [ADR-001](decisions/001-checklist-driven-onboarding.md).
- Onboarding adalah aggregate root untuk progress dan completion invariant.
- State transition eksplisit, bukan generic status update.
- Employee Documents/DMS tetap owner binary evidence.
- Default scope tidak membuat side effect ke Attendance atau Payroll.

## Phase 1 — Contract and template foundation

1. Scaffold module, manifest, permissions, enums, dan migration dasar.
2. Implement checklist template serta ordered items.
3. Tutup authorization, uniqueness, archive/restore, dan template-use rules.

### Checkpoint A — Template contract

- Module validator dan migration up/down hijau.
- Template dapat dibuat dan dibaca secara deterministic.
- Used template tidak dapat di-hard-delete.

## Phase 2 — First vertical slice

4. Implement create/list draft onboarding dengan task snapshot atomic.
5. Tambahkan detail typed UI dan progress awal.
6. Tambahkan duplicate active/employment-context guard dan audit.

### Checkpoint B — Draft snapshot

- Edit template tidak mengubah onboarding existing.
- Failure menyalin salah satu task me-rollback seluruh case.
- Authorized HR dapat membuat dan melihat draft end-to-end.

## Phase 3 — Task and onboarding lifecycle

7. Implement activate onboarding.
8. Implement assignment serta complete/reopen task.
9. Implement controlled skip dan progress calculation.
10. Implement complete dan cancel onboarding.

### Checkpoint C — Lifecycle complete

- Invalid transition tidak membuat partial write.
- Required incomplete memblokir completion.
- Audit actor/time/reason tersedia tanpa PII berlebih.

## Phase 4 — Operational readiness

11. Implement list filters, overdue query/command, archive/restore.
12. Lengkapi frontend state, empty/error state, accessibility, dan responsive behavior.
13. Formalisasikan integration event/schema hanya setelah consumer disetujui.

### Final checkpoint

- Seluruh acceptance criteria pada [specification](specification.md) hijau.
- Full quality gates dan security review hijau.
- README/spec/plan/tasks/ADR mencerminkan implementasi aktual.
- HR roadmap diperbarui tanpa mengklaim non-MVP sebagai selesai.

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Template edit mengubah histori | Evidence onboarding tidak terpercaya | Snapshot task saat create |
| Duplicate onboarding aktif | Dua owner/progress berbeda untuk periode sama | Unique domain guard + transaction/locking |
| Completion balapan dengan task update | Case selesai saat required task belum terminal | Recheck + lock di transaction completion |
| Contract berubah/diarsipkan | Employment context ambigu | Simpan reference dan minimal snapshot context |
| Due date timezone drift | Overdue berbeda antar layar/command | Explicit business date dan date-only semantics |
| Scope menjadi workflow engine | Delivery lambat dan abstraksi prematur | Batasi state/category/assignment sesuai spec |
| Evidence file bocor | Duplikasi storage/security | Gunakan Employee Documents/DMS reference |

## Rollback strategy

- Setiap task menjadi increment terpisah dan buildable.
- Migration awal additive dengan `down()` tervalidasi sebelum production data.
- Setelah production data ada, rollback memakai disable navigation/module dan forward migration; jangan drop history.
- Integration event tidak dipublikasikan sebelum schema version disetujui.

## Parallelization guidance

- Sequential: schema -> template contract -> snapshot create -> lifecycle.
- Dapat paralel setelah contract stabil: frontend read-only detail dan overdue query tests.
- Perlu koordinasi: permission registry, shared Employee/Contract interface, dan integration event.
