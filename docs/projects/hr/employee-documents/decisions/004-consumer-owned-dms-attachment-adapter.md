# ADR-004: Consumer-owned adapter memakai public DMS ingestion gateway

## Status

Accepted dan implemented — 2026-07-14.

## Context

Employee Documents membutuhkan adapter upload ke DMS. Menempatkan adapter bernama dan bertipe HR di dalam foundation DMS akan membuat provider generik mengimpor contract/internal HR, membalik arah dependency, dan memperbesar coupling data employee.

## Decision

- DMS mengekspor contract generik `DocumentIngestionGateway` v1 dan tetap memiliki seluruh ingestion/storage behavior.
- HR memiliki port `EmployeeDocumentAttachmentGateway` serta adapter `DocumentManagementEmployeeDocumentAttachmentAdapter` yang bergantung hanya pada public DTO/contract DMS.
- Input HR hanya membawa ID metadata Employee Document, upload intent, actor reference, idempotency key, dan stream. Adapter membentuk owner context tetap `HR / EmployeeDocument / <metadata-id>`.
- Nama employee, employee ID profile, nomor dokumen, issuer, notes, path, URL, checksum, MIME hasil deteksi, dan storage key tidak melewati boundary sebagai metadata HR.
- Success hanya mengembalikan opaque reference setelah DMS memberi status `AVAILABLE`. Timeout, error storage, atau hasil selain `AVAILABLE` menjadi `EmployeeDocumentAttachmentUnavailable`.
- Adapter tidak menulis `document_reference`; transaction attach/detach HR tetap Task 09/Task 11 berikutnya.

## Consequences

- Dependency tetap searah HR → public DMS contract.
- Retry identik menggunakan idempotency key DMS dan tidak menghasilkan document/version/object kedua.
- Adapter tidak dapat membuat partial reference HR karena belum memiliki akses model/repository HR.
- Persistensi reference, authorization HTTP, detach, dan UI bukan bagian Task 10 foundation.

## Verification

```bash
php artisan test --filter=EmployeeDocumentAttachmentGateway
php artisan module:validate
```

Lihat [Employee Documents tasks](../tasks.md) dan [DMS Task 10](../../../document-management/tasks.md).
