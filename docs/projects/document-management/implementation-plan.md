# Implementation Plan: Document Management Foundation

## Overview

Foundation dibangun contract-first dan risk-first. Binary ingestion tidak dimulai sebelum private storage, upload policy, idempotency, dan failure cleanup dibuktikan. Setiap checkpoint harus meninggalkan aplikasi buildable dan tidak membuka akses file setengah jadi.

## Dependency graph

```txt
Contract + ADR + module boundary
              |
              v
Logical document + owner context + opaque reference
              |
              v
Private StorageAdapter + fake contract tests
              |
              v
Upload policy + staged ingestion + checksum + idempotency
              |
              v
Immutable version + archive/restore
              |
              v
Access decision + delivery handoff
              |
              v
HR adapter -> Employee Documents Task 09 -> Task 10
```

## Phase 1 — Contract dan safe foundation

1. Formalisasi module manifest, permission namespace, DTO/schema v1, dan architecture tests.
2. Tambahkan logical document metadata dan owner-context lookup tanpa binary route.
3. Definisikan `StorageAdapter` private serta fake adapter; buktikan tidak ada path exposure.

### Checkpoint A

- Module validation dan migration tests hijau.
- Opaque reference/idempotency contract disetujui.
- Tidak ada public disk, upload route, atau consumer model import.

## Phase 2 — Vertical slice ingestion

4. Terapkan ADR-002/ADR-003: 20 MiB, PDF/JPEG/PNG, extension/MIME/magic-byte match, polyglot rejection, serta explicit `scan_status=NOT_CONFIGURED` untuk MVP tanpa scanner.
5. Implement staged stream ingestion + SHA-256 + cleanup failure menggunakan fake storage lebih dahulu.
6. Implement private local adapter integration, publish version pertama, dan immutable replacement secara atomic/idempotent.

### Checkpoint B

- Valid upload menghasilkan satu reference/version; retry tidak duplicate.
- Spoofed/oversized/interrupted input gagal tanpa active partial record.
- Storage key dan binary tidak muncul di response/audit.
- MVP single-server boleh menghasilkan `AVAILABLE` dengan `scan_status=NOT_CONFIGURED`; status `CLEAN`, inline preview, parser, dan multi-server tetap nonaktif.

## Phase 3 — Lifecycle dan secure access

7. Implement archive/restore dan read-only descriptor di atas immutable version yang telah diselesaikan pada Phase 2.
8. Implement access decision policy dengan global denial matrix.
9. Implement delivery handoff scoped, short-lived, non-replay sesuai keputusan deployment.

### Checkpoint C

- IDOR/denied/archived/missing/unavailable/revoked/expired seluruhnya fail-closed.
- Archive/detach tidak melakukan permanent delete.
- Audit tidak mengandung token/path/object key.

## Phase 4 — Consumer integration

10. Implement adapter `EmployeeDocumentAttachmentGateway` terhadap DMS contract.
11. Jalankan HR Task 09 attach/detach dengan failure injection dan idempotency. ✅
12. Jalankan HR Task 10 secure access handoff end-to-end. ✅

### Final checkpoint foundation

- Semua acceptance criteria specification hijau.
- Full quality gates, dependency audit, security review, dan restore/backup compatibility hijau.
- Roadmap fitur lanjutan tetap nonaktif sampai foundation production-ready.

## Risks dan mitigasi

| Risk | Impact | Mitigation |
|---|---|---|
| Path traversal / MIME spoofing | RCE atau data exposure | Server-generated object key, basename normalization, magic-byte allowlist |
| Partial storage write | Broken reference/orphan | Staged status, transaction boundary, cleanup/reconciliation |
| Retry membuat duplicate | Biaya dan histori salah | Hashed idempotency key + request fingerprint + unique constraint |
| Public URL bocor | Authorization bypass | Private disk, DMS delivery only, path architecture test |
| Malware tanpa scanner MVP | Compromise endpoint/user | Risk acceptance ADR-003, strict format validation, private attachment-only delivery, no inline preview/parser, audit, dan evaluasi wajib |
| Large stream exhaustion | DoS | Request/body limits, streaming, bounded size/timeouts |
| Consumer menghapus blob | Data loss lintas domain | Detach != delete; retention ownership DMS |
| Token replay | Unauthorized download | Short TTL, scope action/reference/actor, optional single-use record |

## Rollback strategy

- Contract/schema/migration additive dan commit per task.
- Sebelum production data, migration `down()` diuji; setelah production, gunakan forward migration dan disable routes/provider.
- Failed ingestion membersihkan staged object; reconciliation command default dry-run.
- Revert HR adapter tidak menghapus document/version DMS.
- Credential/disk change membutuhkan rotation plan; object key tidak boleh bergantung pada hostname/path deployment.
