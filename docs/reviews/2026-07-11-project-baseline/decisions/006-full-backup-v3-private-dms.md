# ADR-006: Full Backup V3 mencakup private DMS

## Status

Accepted — 2026-07-14.

## Context

Full-backup v2 mencakup database dan `storage_public`. Setelah Document Management menjadi owner binary pada `storage/app/private/document-management`, restore v2 dapat menghasilkan metadata/reference DMS tanpa binary. Kondisi tersebut bukan recovery lengkap dan harus gagal secara eksplisit.

## Decision

- Manifest full backup dinaikkan ke versi 3.
- Exact signed payload mencakup `database.sql`, `storage_public/*`, dan `storage_dms_private/*`.
- Restore storage memulihkan kedua namespace ke root yang tetap: `storage/app/public` dan `storage/app/private/document-management`.
- Archive tetap melewati exact-entry checksum, HMAC authenticity, entry/size bound, dan path traversal validation sebelum write.
- Full-backup v1/v2 ditolak oleh restore v3; operator harus membuat recovery point baru.

## Consequences

- Backup yang dibuat sebelum v3 tidak dianggap sebagai recovery point DMS lengkap.
- Batas upload restore aplikasi menjadi 512 MiB dan harus diselaraskan dengan PHP/web server.
- Database/filesystem restore tetap bukan transaction lintas resource; maintenance window dan restore drill wajib.
- Strategi multi-server/object storage memerlukan format/adapter backup baru.

## Related

- [ADR-004: Full Backup V2 Security Boundary](004-full-backup-v2-security-boundary.md)
- [ADR-005: Cross-environment Backup Signatures](005-cross-environment-backup-signatures.md)
- [Checkpoint C DMS](../../../projects/document-management/checkpoint-c-secure-dms-foundation.md)
