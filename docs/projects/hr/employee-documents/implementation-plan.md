# Implementation Plan: Employee Documents

## Overview

Implementasi dimulai metadata-only untuk membuktikan klasifikasi, expiry, verification, dan authorization. Boundary attachment ditambahkan setelah interface DMS stabil; tidak ada temporary file storage di HR.

## Dependency graph

```txt
Employees + HR Reference Data
              │
              ▼
Metadata schema + sensitive-field policy
              │
              ▼
Create/list metadata ──> expiry query ──> verification lifecycle
              │                                  │
              └──────── archive/restore ─────────┘
                                                 │
Document Management contract ──> attachment gateway + access boundary
```

## Architecture decisions

- Metadata HR dan storage DMS dipisahkan; lihat [ADR-001](decisions/001-hr-metadata-dms-storage-boundary.md).
- Expiry adalah derived state, bukan verification status.
- Sensitive document number tidak masuk audit/event plaintext.
- Tidak ada force delete dan tidak ada cascade delete lintas project.
- Integration contract dibuat sebelum adapter; fake adapter dipakai pada contract tests.

## Phase 1 — Foundation dan vertical slice metadata

1. Formalisasi document type metadata pada HR Reference Data. ✅
2. Scaffold module, schema metadata, permission, dan sensitive-data policy. ✅
3. Implement create/list paginated untuk metadata tanpa attachment. ✅

Authorization matrix untuk vertical slice metadata selesai pada Task 03 dan menjadi gate setiap mutation baru. ✅

Deterministic expiry query dan list filter selesai pada Task 04 tanpa menyimpan expiry sebagai lifecycle status. ✅

Verification lifecycle verify/reject/resubmit serta material-change reset selesai pada Task 05. ✅

Soft-delete archive/restore dengan invariant revalidation dan tanpa operasi DMS selesai pada Task 06. ✅

Read-only expiring command dengan explicit date/window dan privacy-safe output selesai pada Task 07. ✅

Checkpoint B approved pada 2026-07-13 setelah sensitive-data review dan seluruh quality gate hijau. Metadata lifecycle complete; integrasi DMS tetap berada di phase/approval terpisah. ✅

DMS reference contract v1 selesai pada Task 08: interface/DTO/schema/fake tersedia tanpa production binding, route attachment, atau storage implementation. ✅

### Checkpoint 1

- User berizin dapat membuat dan melihat metadata.
- Required-expiry, masking, audit redaction, dan denial matrix teruji.
- Module validator, Pint, typecheck, dan build hijau.

## Phase 2 — Expiry dan verification

4. Tambahkan expiry query dengan tanggal/window eksplisit.
5. Tambahkan verify/reject/resubmit atomic dengan actor dan reason.
6. Reset verification ketika field material berubah.

### Checkpoint 2

- Boundary expiry deterministik.
- State transition matrix dan retry denial teruji.
- Tidak ada nomor dokumen sensitif pada audit/event.

## Phase 3 — Data lifecycle

7. Tambahkan archive/restore dan filter trash tanpa force delete.
8. Tambahkan command expiring read-only tanpa notification side effect.

### Checkpoint 3

- Restore mempertahankan histori dan menjalankan invariant kembali.
- Command output/exit code deterministik serta read-only.

## Phase 4 — DMS boundary

9. Definisikan interface, DTO owner context v1, dan fake adapter.
10. Implement attach/detach workflow serta state unavailable tanpa file write HR. ✅
11. Implement download handoff yang selalu meminta authorization DMS. ✅

### Checkpoint final

- Missing, denied, timeout, retry, dan partial failure contract tests hijau.
- Architecture test membuktikan HR tidak mengimpor model/storage internal DMS.
- Full quality gates dan security review hijau.

## Risks and mitigations

| Risk | Impact | Mitigation |
|---|---|---|
| PII bocor melalui audit/search | High | Encryption/masking, keyed fingerprint, redacted audit contract |
| Duplicate storage engine | High | Larang media collection/path/file fields di HR melalui review/test arsitektur |
| DMS belum tersedia | Medium | Metadata-only slice dan nullable opaque reference |
| Orphan document saat timeout | High | Idempotency key, owner context, reconciliation; desain rinci sebelum Task 10 |
| HR permission membypass DMS | High | Authorization ulang oleh DMS pada setiap file operation |
| Reference hilang/archived | Medium | State `UNAVAILABLE`, tanpa auto-delete metadata |
| Expiry dan verification tercampur | Medium | Dua state terpisah dan boundary tests |

## Rollback strategy

- Setiap task menjadi commit/slice terpisah.
- Migration awal additive dan memiliki `down()` hanya sebelum data production.
- Setelah production, rollback melalui disable navigation/module dan forward migration, bukan drop table.
- Attachment routes tidak didaftarkan sebelum gateway siap.
- Failure attach tidak boleh menyisakan reference parsial di HR.

## Approval checkpoint

Task 01 telah disetujui dan selesai. Task berikutnya tetap dijalankan hanya setelah instruksi manusia; review khusus masih diperlukan sebelum menetapkan encryption, uniqueness, dan DMS ownership release.
