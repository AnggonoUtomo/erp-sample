# Spec: Document Management

## Objective

Menyediakan penyimpanan file lintas project yang private-by-default, versioned, searchable, memiliki retention policy, dan dapat diaudit.

## Submodule dan urutan

1. `DocumentLibraries`, `DocumentTypes`, `MetadataSchemas`.
2. `Documents` dan `DocumentVersions`.
3. `DocumentPermissions` dan `DocumentShares`.
4. `DocumentWorkflows` dan `DocumentApprovals`.
5. `RetentionPolicies`, `LegalHolds`, `DocumentSearch`.
6. `DocumentIntegrations` dan `DocumentReports`.

## Requirements

- File private by default; download memakai authorization setiap request dan URL berumur pendek.
- Validasi size, MIME, magic bytes, checksum, malware-scan state, dan filename normalization.
- Version append-only; retention/legal hold mencegah deletion.
- Domain HR/CRM memiliki metadata/reference bisnis, DMS memiliki blob/version/share/retention.

## Non-scope

Employee contract semantics, CRM quote semantics, collaborative rich-text editor, public CDN, dan OCR provider tertentu pada slice awal.

## Command design

`documents:scan`, `documents:verify-checksums`, `documents:apply-retention`, dan `documents:reindex` harus resumable, idempotent, observable, dan mendukung `--dry-run` untuk operasi retention.

## Acceptance criteria dan test plan

- Unauthorized upload/download/share ditolak; spoofed MIME dan oversized file ditolak; checksum mismatch terdeteksi; version/retention/legal hold, signed URL expiry, malware quarantine, cross-domain ownership, serta retry commands teruji.

## Risks

IDOR, malware, storage exhaustion, leaked signed URL, orphan blob, premature deletion, dan duplikasi metadata domain.
