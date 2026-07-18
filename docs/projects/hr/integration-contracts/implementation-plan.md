# Implementation Plan: HR Integration Contracts

## Overview

Implementasi dibuat kecil dan incremental. Fokus pertama adalah registry dan snapshot read-only; event publisher baru ditambahkan setelah privacy guard dan contract tests hijau.

## Dependency graph

```txt
Existing HR modules
  Employees + master HR data
  EmployeeContracts
  EmployeeDocuments
  EmployeeMovements
  Onboardings
  Offboardings
          │
          ▼
Contract DTO v1 + forbidden field policy
          │
          ▼
Snapshot providers read-only
          │
          ▼
Contract registry + describe/validate commands
          │
          ▼
Event envelope + publisher mapping
          │
          ▼
Consumer documentation for Attendance/Payroll
```

## Architecture decisions

- Integration Contracts adalah boundary internal HR, bukan menu user.
- Contract dibuat versioned dan additive.
- Snapshot provider membaca data source module, tetapi consumer tidak boleh membaca model internal source secara bebas.
- Payload default minim PII.
- Event delivery downstream ditunda sampai modul consumer ada.
- CLI command bersifat non-mutating.

Lihat [ADR-001](decisions/001-stable-hr-integration-contracts.md).

## Phase 1 — Contract foundation

1. Buat module/documented boundary `HR/IntegrationContracts`. ✅
2. Buat registry contract awal. ✅
3. Buat DTO/envelope v1 dan forbidden-field policy. ✅
4. Buat forbidden-field guard dan tests privacy. ✅

### Checkpoint A — Contract registry

- Registry menampilkan semua contract v1.
- Privacy tests hijau.
- Tidak ada route/menu/mutation baru.

## Phase 2 — Snapshot providers

4. Implement `EmployeeSnapshotV1`. ✅
5. Implement `EmployeeAssignmentSnapshotV1` dengan tanggal acuan. ✅
6. Implement contract/document compliance snapshot minimal. ✅

### Checkpoint B — Read-only snapshots

- Provider menghasilkan payload deterministic.
- Archived/terminated handling jelas.
- Tests membuktikan tidak ada write side effect.

## Phase 3 — Events dan commands

7. Tambahkan event envelope v1 dan mapping publisher dari source module.
8. Tambahkan command `describe`, `validate`, dan `sample`.
9. Update module manifest agar contract bisa ditemukan tool/agent/developer.

### Checkpoint C — Event contract ready

- Event payload shape stabil.
- Command output aman.
- Tidak ada listener downstream spekulatif.

## Phase 4 — Consumer handoff

10. Dokumentasikan cara Attendance membaca assignment snapshot.
11. Dokumentasikan cara Payroll membaca employee/contract/termination snapshot.
12. Buat final quality checkpoint dan update roadmap.

### Final checkpoint

- Semua task MVP selesai.
- Full relevant quality gate hijau.
- HR Integration Contracts siap menjadi prasyarat Attendance/Payroll MVP.

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| Payload terlalu banyak PII | High | Forbidden-field guard dan explicit allowlist per DTO |
| Consumer tetap query table internal | High | Dokumentasikan boundary dan beri provider resmi |
| Contract berubah breaking | High | Versioned DTO; field baru additive |
| Event dikirim sebelum transaction commit | Medium | Publisher dipasang setelah commit atau di service layer final state |
| Over-engineering menjadi event bus besar | Medium | MVP tanpa queue/outbox/listener downstream |
| Date semantics beda antar modul | Medium | Semua snapshot menerima `asOf/effectiveDate` eksplisit |

## Rollback strategy

- Task awal hanya additive dan mostly read-only.
- Jika contract salah, buat `V2` dan deprecate `V1`; jangan ubah field v1 secara breaking.
- Jika event publisher bermasalah, disable publisher mapping tanpa menghapus DTO/schema.
- Jika consumer belum siap, contract tetap bisa hidup sebagai dokumentasi dan test boundary.

## Approval checkpoint

Coding dimulai hanya setelah specification dan ADR-001 disetujui. Jika user memilih route JSON internal atau queue/outbox sejak awal, specification harus direvisi dulu.
