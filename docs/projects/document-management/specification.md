# Specification: Document Management Foundation

## Status

Accepted — 2026-07-13.

Implementasi dimulai berurutan dari Task 01. Acceptance ini tidak mengaktifkan ingestion production sebelum seluruh security dan deployment gate pada ADR-001 disetujui.

## 1. Objective

Membangun foundation `DocumentManagement` sebagai owner tunggal logical document, binary version, private storage, integrity, dan authorization file untuk seluruh project ERP. Consumer pertama adalah `HR/EmployeeDocuments`, tetapi contract tidak boleh khusus HR.

Keberhasilan berarti domain consumer dapat mengirim owner context minimal dan upload intent, menerima opaque reference secara idempotent, lalu meminta access decision/delivery tanpa membaca model, tabel, disk, atau path DMS.

## 2. Target user dan assumptions

1. Pengguna operasional awal adalah HR manager/officer melalui UI Employee Documents; DMS foundation sendiri belum membutuhkan workspace lengkap.
2. Session user aplikasi menjadi actor, tetapi identity lintas contract direpresentasikan secara minimal dan versioned.
3. Semua binary berada pada private disk melalui storage adapter; tidak ada public filesystem URL.
4. Logical document memiliki satu atau lebih immutable versions; replace menghasilkan versi baru, bukan overwrite blob lama.
5. Checksum adalah integrity control, bukan authenticity signature.
6. Production MVP memakai private local disk single-server. Upload dibatasi 20 MiB dan PDF/JPEG/PNG sesuai ADR-002; ADR-003 menerima risiko tanpa scanner dan mewajibkan `scan_status=NOT_CONFIGURED` serta compensating controls.

## 3. Requirements

### 3.1 Logical document dan ownership

- Membuat logical document dari `ownerContext` versioned dan idempotency key.
- Owner context v1 hanya memuat `schemaVersion`, `domain`, `aggregateType`, dan `aggregateId`.
- Menghasilkan opaque reference maksimal 255 karakter; consumer tidak menafsirkan formatnya.
- Satu logical document dapat memiliki banyak immutable versions dan tepat satu current version.
- Owner tidak dipindahkan lintas aggregate tanpa use case dan audit eksplisit.

### 3.2 Upload intent dan ingestion

- Upload intent memuat filename display, declared media type, byte size, dan idempotency key; tidak memuat storage path.
- Server memvalidasi size, extension, declared MIME, detected magic bytes, dan stream readability sebelum publish version.
- Write memakai staged/private object; metadata version menjadi visible hanya setelah storage write dan checksum sukses.
- Retry dengan owner context + idempotency key yang sama mengembalikan reference/version yang sama.
- Kegagalan/timeout tidak meninggalkan reference aktif ke object yang tidak lengkap; staged orphan dapat direconcile terpisah.
- Pada MVP tanpa scanner, file structurally valid boleh `AVAILABLE` hanya dengan `scan_status=NOT_CONFIGURED`; status `CLEAN` dilarang tanpa scan aktual.

Upload policy foundation:

| Control | Decision |
|---|---|
| Maximum size | 20 MiB (`20,971,520` byte) |
| Extensions | `pdf`, `jpg`, `jpeg`, `png` |
| MIME | `application/pdf`, `image/jpeg`, `image/png` |
| Detection | Extension, declared MIME, dan magic-byte wajib cocok |
| Polyglot/ambiguous | Leading signature harus tunggal/dikenal dan terminal marker harus berada di akhir tanpa trailing payload; parser penuh bukan bagian MVP |
| Scanner MVP | Tidak dikonfigurasi; `AVAILABLE` memakai `scan_status=NOT_CONFIGURED` dan risk acceptance ADR-003 |
| Staged reconciliation | Setelah 24 jam, bounded dan dry-run default |

### 3.3 Integrity dan versioning

- Setiap version menyimpan server-computed SHA-256, byte size, detected media type, original display filename, storage object key internal, actor, dan timestamp.
- Storage object key tidak pernah menjadi public contract atau dikirim ke consumer.
- Binary version tidak dioverwrite; replacement membuat version number berikutnya secara atomic.
- Download/preview memverifikasi keberadaan object; checksum revalidation asynchronous adalah task lanjutan.

### 3.4 Access decision dan delivery

State contract:

```txt
AVAILABLE | MISSING | ARCHIVED | UNAVAILABLE | DENIED
```

- Metadata permission consumer tidak otomatis memberi akses binary.
- DMS mengevaluasi actor, owner context, action (`VIEW`/`DOWNLOAD`), document state, dan policy.
- `MISSING`, `ARCHIVED`, `UNAVAILABLE`, `DENIED`, revoked access, serta unknown action selalu fail-closed.
- Delivery dilakukan oleh DMS melalui endpoint authorized atau short-lived single-purpose delivery token; consumer tidak membuat URL storage.
- Delivery token tidak disimpan di database consumer, tidak masuk log, dan tidak dapat dipakai untuk action/document lain.

### 3.5 Archive dan detach semantics

- Detach consumer hanya melepas hubungan/reference pada consumer; tidak otomatis menghapus logical document/blob.
- Archive logical document memakai soft delete/state transition dan mencatat actor/reason.
- Permanent deletion, retention expiration, legal hold, dan release ownership bukan bagian foundation.

### 3.6 Audit dan observability

- Audit: create logical document, version stored, access denied/granted, archive/restore, dan delivery issued.
- Audit tidak memuat binary, token, storage key, credential, atau isi file.
- Structured log menggunakan correlation/idempotency reference yang aman, bukan filename sensitif bila tidak diperlukan.

## 4. Contract v1

```txt
DocumentReferenceReader
  describe(reference, actorReference): DocumentReferenceDescriptorV1

EmployeeDocumentAttachmentGateway
  createFor(ownerContext, uploadIntent, actorReference): AttachmentResultV1
  detach(reference, ownerContext, actorReference): DetachResultV1

DocumentAccessGateway
  decide(reference, actorReference, action, expectedOwner): AccessDecisionV1

DocumentDeliveryGateway
  issue(request): DeliveryHandoffV1
```

Contract harus additive dan versioned. Result menggunakan discriminated state, bukan campuran `null`, boolean, dan exception. Exception hanya untuk transport/system failure; denial bisnis dikembalikan sebagai state `DENIED`.

## 5. Data contract awal

### `dm_documents`

| Field | Contract |
|---|---|
| `id` | Internal primary key; bukan public reference |
| `reference` | Opaque, unique, immutable |
| owner context fields | Version/domain/aggregate type/id, indexed |
| `current_version_id` | Nullable sampai version pertama published |
| `status` | `PENDING`, `AVAILABLE`, `ARCHIVED` |
| `created_by` | Actor internal |
| timestamps + `deleted_at` | Lifecycle teknis |

### `dm_document_versions`

| Field | Contract |
|---|---|
| `document_id`, `version_number` | Unique per logical document |
| `storage_object_key` | Internal only, encrypted/config-protected bila diperlukan |
| `original_filename` | Display metadata, normalized dan bounded |
| `declared_media_type`, `detected_media_type` | Tidak mempercayai client declaration |
| `byte_size` | Server-observed size |
| `sha256` | Server-computed integrity hash |
| `status` | `STAGED`, `AVAILABLE`, `QUARANTINED`, `FAILED` |
| `scan_status` | `NOT_CONFIGURED` pada MVP; tidak boleh `CLEAN` tanpa scan aktual |
| `created_by`, timestamps | Audit actor/time |

### `dm_idempotency_keys`

Menyimpan scope, key hash, request fingerprint, result reference/version, status, dan expiry. Raw idempotency key tidak disimpan/log.

## 6. Non-scope foundation

- Folder tree, category master, tags, full-text search, OCR, document editor, sharing link, approval workflow, e-signature, comments, favorites, dan bulk import.
- Automated retention deletion, legal hold engine, records management, permanent deletion, dan cross-region replication.
- Public API, mobile SDK, WebDAV, S3-compatible API, atau direct database access consumer.
- Antivirus vendor implementation pada MVP; risk acceptance dan compensating controls mengikuti ADR-003.
- HR verification/expiry metadata; itu tetap milik Employee Documents.

## 7. Project structure

```txt
app/Modules/DocumentManagement/Foundation/
  Console/  Database/Migrations/  DTO/  Http/Controllers/  Http/Requests/
  Integration/Contracts/  Integration/DTO/  Models/  Policies/
  Providers/  Services/  Storage/Contracts/  Storage/Adapters/  Transactions/
  module.php  routes.php  permissions.php
tests/Feature/DocumentManagementFoundationTest.php
tests/Feature/DocumentManagementAccessTest.php
tests/Feature/DocumentManagementContractTest.php
docs/projects/document-management/
```

## 8. Route dan command design

Route internal DMS baru diaktifkan setelah security checkpoint:

```txt
POST /document-management/documents
POST /document-management/documents/{reference}/versions
POST /document-management/documents/{reference}/delivery
POST /document-management/deliveries/consume
DELETE /document-management/documents/{reference}
PATCH /document-management/documents/{reference}/restore
```

Tidak ada route berdasarkan storage path. Opaque reference selalu melalui route model binding/service lookup dan policy.

Command foundation:

```bash
php artisan dms:reconcile-staged --older-than=60 --dry-run
php artisan dms:verify-integrity --reference=<opaque> --dry-run
```

Command mutasi default `--dry-run`, idempotent, bounded, dan tidak menghapus object permanent.

## 9. Code style dan boundaries

- Ikuti `FormRequest -> DTO -> Service -> Transaction -> Model`, dengan binary I/O melalui `StorageAdapter` interface.
- Selalu: private storage, server-side policy, streaming I/O, size limit, magic-byte validation, checksum, idempotency, audit, soft delete, dan failure cleanup.
- Ask first: migration/schema final, perubahan disk/topology, upload limit/allowlist, penambahan scanner, perubahan token TTL dari keputusan ADR-004, queue, retention, encryption key, dan public event.
- Never: public disk untuk regulated document, menyimpan binary di database, mempercayai MIME client, mengirim path/object key ke consumer, direct model import lintas project, atau hard delete tanpa retention/legal-hold decision.

## 10. Acceptance criteria foundation

- Module contract valid dan tidak mengaktifkan public storage.
- Upload valid menghasilkan logical document + immutable version + checksum + opaque reference secara atomic/idempotent.
- Invalid size/type/magic bytes, interrupted stream, storage failure, dan duplicate retry tidak menghasilkan partial active document.
- Access decision dan delivery fail-closed untuk IDOR, denied, archived, missing, unavailable, revoked, serta expired token.
- Consumer HR dapat memakai adapter contract tanpa mengimpor model/schema/path DMS.
- Archive/detach tidak menghapus binary permanent.
- Full backend/frontend/module/security quality gates hijau sebelum Task 09 HR dibuka kembali.

## 11. Testing strategy

- Contract: owner context, opaque reference, idempotency, discriminated failures, additive schema v1.
- Feature: ingestion success/failure, immutable version, archive/restore, actor audit.
- Security: IDOR, permission denial matrix, token scope/expiry/replay, filename traversal, MIME spoofing, oversized stream.
- Storage adapter: fake deterministic contract tests; private local adapter integration test; production driver smoke test di environment terkait.
- Failure injection: storage timeout/write failure/checksum mismatch dan cleanup staged object.
- Architecture: larangan path/URL exposure, binary DB column, public disk, dan cross-project model import.

```bash
php artisan module:validate
vendor/bin/pint --test
php artisan test --filter=DocumentManagement
npm run lint:check
npm run format:check
npm run typecheck
npm run test:frontend
npm run build
php artisan test
git diff --check
```

## 12. Remaining deployment questions

- Evaluasi pasca-MVP: scanner vendor/mode, timeout, retry, signature update, quarantine, dan re-scan existing objects?
- Evaluasi pasca-MVP controller delivery: streaming concurrency/timeout, range request, resume, serta migrasi object storage?
- Database production MySQL/PostgreSQL dan strategi locking/idempotency final?
- Apakah document tanpa consumer attachment boleh direconcile/expire setelah grace period?
- Retention duration dan ownership legal hold/permanent deletion?
- Trigger migrasi dari private-local single-server menuju object storage/multi-server?
