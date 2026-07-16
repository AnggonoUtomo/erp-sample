# ADR-004: Approval, Archive, and Applied Event

## Status

Accepted — 2026-07-17.

## Context

Employee Movement mengubah current profile Employees. Tanpa approval gate, user dengan apply permission dapat langsung mengubah profile dari draft yang belum direview. Module juga membutuhkan cara menutup movement yang batal tanpa hard delete, serta contract integration minimal untuk downstream tanpa mengekspos detail internal.

## Decision

Lifecycle v1 menjadi:

```txt
DRAFT -> APPROVED -> APPLIED
   \         \
    \         -> CANCELLED -> archived/restored
     -> CANCELLED -> archived/restored
```

Aturan:

- `APPROVED` wajib sebelum apply manual maupun command scheduler.
- Approval mengulang stale/profile guard agar draft lama tidak dapat disetujui setelah profile berubah.
- `CANCELLED` dapat di-archive dengan soft delete dan dapat restore.
- `APPLIED` tidak di-archive pada v1 agar histori perubahan profile tetap terlihat.
- Saat apply sukses, module menerbitkan `EmployeeMovementAppliedV1`.

Payload event v1 hanya memuat:

- `movementId`
- `employeeId`
- `movementType`
- `effectiveDate`
- `changedFields`
- `approvedBy`
- `appliedBy`

Payload tidak memuat nama employee, reason, notes, snapshot value, document reference, atau PII bebas.

## Alternatives Considered

### Apply langsung dari DRAFT

Ditolak untuk task final karena tidak ada checkpoint review eksplisit.

### Archive semua movement termasuk APPLIED

Ditolak untuk v1 karena applied movement adalah histori perubahan profile dan sebaiknya tetap terlihat.

### Event membawa full before/after snapshot

Ditolak karena memperbesar coupling downstream dan berisiko membawa data yang tidak perlu.

## Consequences

- Workflow HR lebih aman karena apply wajib melewati approval.
- Cancelled movement dapat dirapikan tanpa kehilangan histori karena soft delete.
- Downstream mendapat sinyal minimal bahwa profile berubah, tetapi harus membaca contract/snapshot resmi jika butuh detail lebih dalam.
